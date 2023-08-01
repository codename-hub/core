<?php

namespace codename\core;

use codename\core\helper\date;
use codename\core\model\timemachineInterface;
use codename\core\model\timemachineModelInterface;
use ReflectionException;

/**
 * timemachine
 * provides the ability to access historic versions of model data
 * @since 2017-03-08
 */
class timemachine
{
    /**
     * instance cache
     * @var timemachine[]
     */
    protected static array $instances = [];
    /**
     * @var null|model
     */
    protected ?model $timemachineModel = null;
    /**
     * a model capable of using the timemachine
     * @var null|model
     */
    protected model|null $capableModel = null;

    /**
     * creates a new instance of the timemachine
     * please use ::getInstance() instead!
     *
     * @param model $capableModel [description]
     * @throws exception
     */
    public function __construct(model $capableModel)
    {
        if (!$capableModel instanceof timemachineInterface) {
            throw new exception('MODEL_DOES_NOT_IMPLEMENT_TIMEMACHINE_INTERFACE', exception::$ERRORLEVEL_FATAL, $capableModel->getIdentifier());
        }
        if (!$capableModel->isTimemachineEnabled()) {
            throw new exception('MODEL_IS_NOT_TIMEMACHINE_ENABLED', exception::$ERRORLEVEL_FATAL, $capableModel->getIdentifier());
        }

        // set the source model (model capable of using the timemachine)
        $this->capableModel = $capableModel;

        // set the associated timemachine model
        // this model is used for storing the delta data
        $this->timemachineModel = $capableModel->getTimemachineModel();

        if (!($this->timemachineModel instanceof timemachineModelInterface)) {
            throw new exception('TIMEMACHINE_MODEL_DOES_NOT_IMPLEMENT_TIMEMACHINEMODELINTERFACE', exception::$ERRORLEVEL_FATAL, $this->timemachineModel->getIdentifier());
        }
    }

    /**
     * get a timemachine instance for a given model name
     *
     * @param string $capableModelName [description]
     * @param string $app [description]
     * @param string $vendor [description]
     * @return timemachine                   [description]
     * @throws ReflectionException
     * @throws exception
     */
    public static function getInstance(string $capableModelName, string $app = '', string $vendor = ''): timemachine
    {
        $identifier = $capableModelName . '-' . $vendor . '-' . $app;
        return self::$instances[$identifier] ?? (self::$instances[$identifier] = new self(app::getModel($capableModelName, $app, $vendor)));
    }

    /**
     * returns a dataset at a given point in time
     *
     * @param int $identifier [description]
     * @param int $timestamp [description]
     * @return array             [description]
     * @throws ReflectionException
     * @throws exception
     */
    public function getHistoricData(int $identifier, int $timestamp): array
    {
        $delta = $this->getDeltaData($identifier, $timestamp);
        $current = $this->getCurrentData($identifier);
        return array_replace($current, $delta);
    }

    /**
     * [getDeltaData description]
     * @param int $identifier [the primary key]
     * @param int $timestamp [the oldest timestamp we're retrieving the data for]
     * @return array             [delta data]
     * @throws ReflectionException
     * @throws exception
     */
    public function getDeltaData(int $identifier, int $timestamp): array
    {
        $history = $this->getHistory($identifier, $timestamp);
        $excludedFields = $this->getExcludedFields();

        $delta = [];
        foreach ($history as $state) {
            $h = $state[$this->timemachineModel->getIdentifier() . '_data'];
            foreach ($h as $key => $value) {
                if (!in_array($key, $excludedFields)) {
                    if ((!array_key_exists($key, $delta)) || ($delta[$key] != $value)) { // TODO: CHECK
                        // value differs or even the key doesn't exist
                        $delta[$key] = $value;
                    }
                }
            }
        }
        return $delta;
    }

    /**
     * returns a history of all changes done to an entry in descending order
     * optionally, until a specific timestamp
     * @param int $identifier [id/primary key value]
     * @param int $timestamp [unix timestamp, default 0 for ALL/until now]
     * @return array
     * @throws ReflectionException
     * @throws exception
     */
    public function getHistory(int $identifier, int $timestamp = 0): array
    {
        $this->timemachineModel
          ->addFilter($this->timemachineModel->getIdentifier() . '_model', $this->capableModel->getIdentifier())
          ->addFilter($this->timemachineModel->getIdentifier() . '_ref', $identifier)
          ->addOrder($this->timemachineModel->getIdentifier() . '_created', 'DESC');

        if ($timestamp !== 0) {
            // return all entries newer than a specific state
            // to go through all entries in descending order
            $this->timemachineModel->addFilter($this->timemachineModel->getIdentifier() . '_created', date::getTimestampAsDbdate($timestamp), '>=');
        }

        // get the history (all respective timemachine entries) for the requested time range
        $history = $this->timemachineModel->search()->getResult();

        // retrieve target datatype
        $datatype = $this->capableModel->config->get('datatype');

        foreach ($history as &$r) {
            foreach ($r as $key => &$value) {
                if (array_key_exists($key, $datatype)) {
                    if (str_contains($datatype[$key], 'structu')) {
                        $value = app::object2array(json_decode($value, false)/*, 512, JSON_UNESCAPED_UNICODE)*/);
                    }
                }
            }
        }

        return $history;
    }

    /**
     * returns the fields excluded from timemachine tracking
     * @return string[]
     * @throws exception
     */
    protected function getExcludedFields(): array
    {
        // by default, exclude the primarykey
        // and both mandatory fields when using schematic models: ..._created, ..._modified
        // TODO: provide an interface for excluding fields via capableModel

        return [
          $this->capableModel->getPrimaryKey(),
          $this->capableModel->getIdentifier() . '_created',
          $this->capableModel->getIdentifier() . '_modified',
        ];
    }

    /**
     * returns the current dataset
     *
     * @param int $identifier [description]
     * @return array             [description]
     * @throws ReflectionException
     * @throws exception
     */
    public function getCurrentData(int $identifier): array
    {
        return $this->capableModel->load($identifier);
    }

    /**
     * saves the delta-based state of a given model and entry
     * and returns the respective entry id or NULL (empty delta)
     * @param int $identifier [description]
     * @param array $newData [description]
     * @param bool $deletion [whether the corresponding dataset is going to be deleted - this stores a full snapshot]
     * @return int|null                [description]
     * @throws ReflectionException
     * @throws exception
     */
    public function saveState(int $identifier, array $newData, bool $deletion = false): ?int
    {
        $data = $this->getCurrentData($identifier);
        $delta = [];
        $excludedFields = $this->getExcludedFields();

        if ($deletion) {
            //
            // for deletion: store the full dataset
            //
            $delta = $data;
        } else {
            //
            // generic delta calculation
            //
            foreach ($newData as $key => $value) {
                if (!in_array($key, $excludedFields)) {
                    if ((!array_key_exists($key, $data)) || ($data[$key] != $value)) {
                        // value differs or even the key doesn't exist
                        $delta[$key] = $data[$key] ?? null; // store EXISTING/old data (!)
                    }
                }
            }
        }

        // do not story empty deltas (no difference)
        if (count($delta) === 0) {
            return null;
        }

        //
        // CHANGED 2020-02-27: omit instanceof for timemachine model,
        // as it might cost cycles,
        // and it is implemented/checked in the constructor anyway
        //
        $this->timemachineModel->save([
          $this->timemachineModel->getModelField() => $this->capableModel->getIdentifier(),
          $this->timemachineModel->getRefField() => $identifier,
          $this->timemachineModel->getDataField() => $delta,
        ]);
        return $this->timemachineModel->lastInsertId();
    }
}
