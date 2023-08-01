<?php

namespace codename\core\model\plugin;

use codename\core\exception;
use codename\core\model;
use codename\core\model\plugin;
use codename\core\value\text\modelfield;
use ReflectionException;

/**
 * Provide many-to-many relationship functionality as a plugin
 * @package core
 * @since 2018-01-08
 */
class collection extends plugin
{
    /**
     * the original model (base)
     * this model's result gets extended by the collection data
     * @var null|model
     */
    public ?model $baseModel = null;

    /**
     * the collection model
     * @var null|model
     */
    public ?model $collectionModel = null;

    /**
     * Field in the original model the data will reside in
     * @var null|modelfield
     */
    public ?modelfield $field = null;
    /**
     * field of the base model
     * that is used as the join counterpart
     * mostly, this should be the PKEY of the base model
     * @var string
     */
    protected mixed $baseField = null;
    /**
     * the field of the collection model
     * that references the base model
     * @var mixed
     */
    protected mixed $collectionModelBaseRefField = null;

    /**
     * Undocumented function
     *
     * @param modelfield $field
     * @param model $baseModel
     * @param model $collectionModel
     * @throws ReflectionException
     * @throws exception
     */
    public function __construct(modelfield $field, model $baseModel, model $collectionModel)
    {
        $this->field = $field;
        $this->baseModel = $baseModel;
        $this->collectionModel = $collectionModel;

        // prepare some data
        foreach ($this->collectionModel->config->get('foreign') as $fkey => $fcfg) {
            if ($fcfg['model'] == $this->baseModel->getIdentifier()) {
                $this->baseField = $fcfg['key'];
                $this->collectionModelBaseRefField = $fkey;

                //
                // FKEY field needs to be set
                // Workaround: simply don't allow NULL values here.
                //
                $this->collectionModel->addDefaultFilter($fkey, null, '!=');
                break;
            }
        }

        if (!$this->baseField) {
            throw new exception('EXCEPTION_MODEL_PLUGIN_COLLECTION_MISSING_BASEFIELD', exception::$ERRORLEVEL_ERROR);
        }
        if (!$this->collectionModelBaseRefField) {
            throw new exception('EXCEPTION_MODEL_PLUGIN_COLLECTION_MISSING_COLLECTIONMODEL_BASEREF_FIELD', exception::$ERRORLEVEL_ERROR);
        }
    }

    /**
     * returns the field name in the base model
     * we're referencing
     *
     * @return string
     */
    public function getBaseField(): string
    {
        return $this->baseField;
    }

    /**
     * returns the field name in the auxiliary model
     * that stores the reference to the base model
     * (-> getBaseField)
     *
     * @return string
     */
    public function getCollectionModelBaseRefField(): string
    {
        return $this->collectionModelBaseRefField;
    }
}
