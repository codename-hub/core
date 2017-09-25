<?php
namespace codename\core\bucketdipper;
use \codename\core\accesscontrol\policy;
use \codename\core\app;
use \codename\core\exception;

/**
 * Access a file in a bucket via a signed url (component)
 * @package core
 * @author Kevin Dargel
 * @since 2016-11-02
 */
class bucketAccess {

  final public static function tryGetFile(int $id, string $signatureHash, array $options = array() ) {
    $model = new \codename\core\model\urlsignature();
    $res = $model->addFilter('urlsignature_id', intval($id))->addFilter('urlsignature_hash', $signatureHash)->search()->getResult();

    if(sizeof($res) == 1) {
      $sig = $res[0];

      // $sig['bucket']
      // $sig['file']
      $policies = $sig['urlsignature_data']['policy'];

      // print_r($policies);

      $bucket = app::getBucket($sig['urlsignature_bucket']);
      $file = new \codename\core\value\text\filerelative($sig['urlsignature_file']);
      $urlsig = new \codename\core\bucketdipper\urlsignature($bucket, $file);
      $urlsig->setParameters($policies);

      foreach($policies as $policyDescriptor => $policyData) {
        // Find policy class
        $class = 'accesscontrol_policy_' . $policyDescriptor; // @TODO Check if you can use non core-NS-Instances
        $policyInstance = app::getInstance($class);
        // print_r($policyData);
        $policyInstance->setParameters($policyData);
        $urlsig->addPolicy($policyInstance);
      }

      // print_r($urlsig);

      // echo(" Current Timestamp: ". time());

      if($urlsig->validate($signatureHash)) {

        // Update policy data, if needed.
        if($urlsig->needsUpdate()) {
          $partialData = array(
            'urlsignature_id' => $sig['urlsignature_id'],
            'urlsignature_data' => array(
              'policy' => $urlsig->getPolicyData()
            ),
            'urlsignature_obsolete' => $urlsig->isObsolete()
          );
          $model->save($partialData);
        }

        $path_parts = pathinfo($sig['urlsignature_file']);

        $bucket->downloadToClient(
          $file,
          new \codename\core\value\text\filename($path_parts['basename']),
          array(
            'inline' => in_array(self::DOWNLOAD_INLINE, $options)
          )
        );
        // die('VALID_SIGNATURE');
      } else {

        // update obsoletion afterwards.
        if($urlsig->isObsolete()) {
          $partialData = array(
            'urlsignature_id' => $sig['urlsignature_id'],
            'urlsignature_obsolete' => true
          );
          $model->save($partialData);
        }

        die('INVALID_SIGNATURE');
      }

    } else {
      throw exception("", 0);
    }
  }


  const DOWNLOAD_INLINE = "DOWNLOAD_INLINE";
  const DOWNLOAD_FORCE = "DOWNLOAD_FORCE";

  final public static function createUrlSignature(string $bucketName, string $filePath, array $policies) {
    return self::createUrlSignatureWithInstance(app::getBucket($bucketName), $bucketName, $filePath, $policies);
  }

  final public static function createUrlSignatureWithInstance(\codename\core\bucket $bucketInstance, string $bucketName, string $filePath, array $policies) {
    $bucket = $bucketInstance;
    $file = new \codename\core\value\text\filerelative($filePath);
    $signature = new \codename\core\bucketdipper\urlsignature($bucket, $file);
    foreach($policies as $policy) {
      $signature->addPolicy($policy);
    }

    $model = new \codename\core\model\urlsignature();

    $signatureData = $signature->get();

    $data = array(
      'urlsignature_bucket' => $bucketName,
      'urlsignature_file' => $filePath,
      'urlsignature_hash' => $signatureData,
      'urlsignature_data' => array(
        'policy' => $signature->getPolicyData()
      ),
    );

    $model->save($data);
    $id = $model->lastInsertId();

    if($id > 0) {
      return array(
        'signature_id' => $id,
        'signature_data' => $signatureData
      );
    } else {
      throw exception("SIGNATURE_NOT_CREATED", 0);
    }

  }

}
