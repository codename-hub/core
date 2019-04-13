<?php
namespace codename\core\bucket;

use Aws\S3\Exception\S3Exception;

use codename\core\app;

/**
 * I can manage files in a S3-compatible filesystem.
 * @package appkit
 * @author Kevin Dargel <kevin@jocoon.de>
 * @since 2017-04-05
 */
class s3 extends \codename\core\bucket implements \codename\core\bucket\bucketInterface {

  /**
   * the current S3 client from AWS SDK
   * @var \Aws\S3\S3Client
   */
  protected $client = null;

  /**
   * the bucket used for this client instance
   * @var string
   */
  protected $bucket = null;

  /**
   * s3 version used for this client instance
   * @var string
   */
  protected $version = '2006-03-01';
  /**
   * AWS region for s3
   * defaults to eu-west-1
   * @var string
   */
  protected $region = 'eu-west-1';

  /**
   * credentials. defaults to null (e.g. for IAM-bases auth with EC2 instances)
   * @var array
   */
  protected $credentials = null;

  /**
   * current acl
   * used for every upload/copy/... command
   * @var string
   */
  protected $acl = self::ACL_PRIVATE;

  /**
   * ACL: Private - needs signed links in frontend
   * @var string
   */
  const ACL_PRIVATE = 'private';

  /**
   * ACL: Public Read
   * @var string
   */
  const ACL_PUBLIC_READ = 'public-read';

  /**
   * actually, there are more ACL options than those two:
   * private | public-read | public-read-write | authenticated-read | bucket-owner-read | bucket-owner-full-control
   */


  /**
   * default prefix for limiting access to the bucket
   * bucketname/prefix...
   * @var string
   */
  protected $prefix = '';

  /**
   * returns the current prefixed path component
   * @return string [description]
   */
  protected function getPathPrefix() : string {
    return $this->prefix != '' ? ($this->prefix . '/') : '';
  }

  /**
   * returns the final prefixed path
   * and omits prepending, if already present.
   * @param string $path
   * @return string
   */
  public function getPrefixedPath(string $path) : string {
    $prefix = $this->getPathPrefix();
    if($path != '') {
      if($prefix == '' || strpos($path, $prefix) !== 0) {
        return $prefix . $path;
      }
    } else {
      $path = $prefix;
    }
    return $path;
  }

  /**
   * sets the current path prefix used for all requests using this instance
   * @param string $prefix [path component] without trailing slash
   */
  public function setPathPrefix(string $prefix) {
    $this->prefix = $prefix;
  }

  /**
   *
   * @param array $data
   */
  public function __construct(array $data) {
      $this->errorstack = new \codename\core\errorstack('BUCKET');
      if(count($errors = app::getValidator('structure_config_bucket_s3')->validate($data)) > 0) {
          $this->errorstack->addError('CONFIGURATION', 'CONFIGURATION_INVALID', $errors);
          throw new \codename\core\exception(self::EXCEPTION_CONSTRUCT_CONFIGURATIONINVALID, 4, $errors);
      }

      $this->bucket = $data['bucket'];
      $this->region = $data['region'] ?? $this->region;
      $this->version = $data['version'] ?? $this->version;
      $this->credentials = $data['credentials'] ?? $this->credentials;
      $this->prefix = $data['prefix'] ?? $this->prefix;

      $factoryConfig = array(
        'version' => $this->version,
        'region'  => $this->region
      );

      // custom endpoint override
      if($data['bucket_endpoint'] ?? false) {
        $factoryConfig['bucket_endpoint'] = $data['bucket_endpoint'];
      }

      if($data['endpoint'] ?? false) {
        $factoryConfig['endpoint'] = $data['endpoint'];
      }

      if($this->credentials != null) {
        $factoryConfig['credentials'] = $this->credentials;
      }


      $this->client = \Aws\S3\S3Client::factory($factoryConfig);

      return $this;
  }

  /**
   * sets the current default ACL for this bucket instance
   * @param  string $acl [description]
   * @return bool        [success of operation]
   */
  public function setAcl(string $acl) : bool {
    if(($acl == self::ACL_PRIVATE) || ($acl == self::ACL_PUBLIC_READ)) {
      $this->acl = $acl;
      return true;
    }
    return false;
  }

  /**
   * @inheritDoc
   */
  public function filePush(string $localfile, string $remotefile): bool
  {
    try{
      $result = $this->client->putObject([
          'Bucket'     => $this->bucket,
          'Key'        => $this->getPrefixedPath($remotefile),
          'SourceFile' => $localfile,
          'ACL'        => $this->acl
      ]);
      return true; // ?
    } catch (S3Exception $e) {
      $this->errorstack->addError('BUCKET', 'S3_EXCEPTION', $e->getMessage());
    }
    return false;
  }

  /**
   * @inheritDoc
   */
  public function filePull(string $remotefile, string $localfile): bool
  {
    try{
      $result = $this->client->getObject([
          'Bucket'     => $this->bucket,
          'Key'        => $this->getPrefixedPath($remotefile),
          'SaveAs'     => $localfile,
      ]);
      return true;
    } catch (S3Exception $e) {
      $this->errorstack->addError('BUCKET', 'S3_EXCEPTION', $e->getMessage());
    }
    return false;
  }

  /**
   * @inheritDoc
   */
  public function fileAvailable(string $remotefile): bool
  {
    return $this->objectExists($this->getPrefixedPath($remotefile));
  }

  /**
   * [objectExists description]
   * @param  string $key [description]
   * @return bool        [description]
   */
  protected function objectExists(string $key) : bool
  {
    try{
      /**
       * @see http://docs.aws.amazon.com/aws-sdk-php/v3/api/class-Aws.S3.S3ClientInterface.html#_doesObjectExist
       */
      return $this->client->doesObjectExist(
        $this->bucket,
        $key
        // optional: options
      );
    } catch (S3Exception $e) {
      $this->errorstack->addError('BUCKET', 'S3_EXCEPTION', $e->getMessage());
      echo($e->getMessage());
    }
    return false;
  }

  /**
   * @inheritDoc
   */
  public function fileDelete(string $remotefile): bool
  {
    try{
      /**
       * @see http://docs.aws.amazon.com/aws-sdk-php/v3/api/api-s3-2006-03-01.html#deleteobject
       */
      $result = $this->client->deleteObject([
        'Bucket' => $this->bucket,
        'Key'    => $this->getPrefixedPath($remotefile),
      ]);
      return true;
    } catch (S3Exception $e) {
      $this->errorstack->addError('BUCKET', 'S3_EXCEPTION', $e->getMessage());
    }
    return false;
  }

  /**
   * @inheritDoc
   */
  public function fileMove(string $remotefile, string $newremotefile): bool
  {
    try{
      /**
       * @see http://docs.aws.amazon.com/AmazonS3/latest/dev/CopyingObjectUsingPHP.html
       */
      $result = $this->client->copyObject([
        'Bucket'     => $this->bucket,
        'Key'        => $this->getPrefixedPath($newremotefile),
        'CopySource' => "{$this->bucket}/". $this->getPrefixedPath($remotefile),
        'ACL'        => $this->acl
      ]);
      // delete file afterwards
      $fileDeleted = $this->fileDelete($this->getPrefixedPath($remotefile));
      return $fileDeleted;
    } catch (S3Exception $e) {
      $this->errorstack->addError('BUCKET', 'S3_EXCEPTION', $e->getMessage());
    }
    return false;
  }

  /**
   * @inheritDoc
   * @param mixed $option [integer|string|DateTime]
   */
  public function fileGetUrl(string $remotefile, $option = '+10 minutes'): string
  {
    /**
     * we may use @see http://docs.aws.amazon.com/aws-sdk-php/v3/api/class-Aws.S3.S3Client.html#_getObjectUrl
     * and @see https://docs.aws.amazon.com/aws-sdk-php/v3/guide/service/s3-presigned-url.html
     */

    if($this->getAccessInfo($remotefile) === self::ACL_PUBLIC_READ) {

      // we may use an alternative URL Provider here.
      try{
        $result = $this->client->getObjectUrl($this->bucket, $this->getPrefixedPath($remotefile));
        return $result;
      } catch (S3Exception $e) {
        $this->errorstack->addError('BUCKET', 'S3_EXCEPTION', $e->getMessage());
      }

    } else {

      // we may use an alternative URL Provider here.
      try{
        $cmd = $this->client->getCommand('GetObject', [
          'Bucket'     => $this->bucket,
          'Key'        => $this->getPrefixedPath($remotefile)
        ]);

        /**
         * $option defines +10 minutes access/request validity upon generation
         * @see http://docs.aws.amazon.com/aws-sdk-php/v3/api/class-Aws.S3.S3Client.html#_createPresignedRequest
         */
        $request = $this->client->createPresignedRequest($cmd, $option);

        // Get the actual presigned-url
        $presignedUrl = (string) $request->getUri();
        return $presignedUrl;

      } catch (S3Exception $e) {
        $this->errorstack->addError('BUCKET', 'S3_EXCEPTION', $e->getMessage());
      }
    }

    return ''; // throw exception?
  }


  /**
   * @inheritDoc
   */
  public function fileGetInfo(string $remotefile): array
  {
    // @TODO: s3-specific fileGetInfo
    return array();
  }

  /**
   * @inheritDoc
   */
  public function dirList(string $directory): array
  {
    try{
      /**
       * @see http://stackoverflow.com/questions/18683206/list-objects-in-a-specific-folder-on-amazon-s3
       */

      $response = $this->client->listObjectsV2([
        "Bucket" => $this->bucket,
        "Prefix" => $this->getPrefixedPath($directory),
        "Delimiter" => '/'
      ]);

      $objects = array();

      //
      // The "Files" (objects)
      //
      $result = $response->get('Contents') ?? false;
      if($result) {
        foreach($result as $object) {
          //
          // HACK:
          // filter out self - s3 outputs the starting (requested) folder, too.
          //
          if($object['Key'] != $directory) {
            $objects[] = $object['Key'];
          }
        }
      }

      if($response['CommonPrefixes'] ?? false) {
        //
        // The "Folders"
        //
        $commonPrefixes = $response->get('CommonPrefixes');
        foreach($commonPrefixes as $object) {
          //
          // HACK:
          // filter out self - s3 outputs also the starting (requested) folder in CommonPrefixes
          //
          if($object['Prefix'] != $directory) {
            $objects[] = $object['Prefix'];
          }
        }
      }
      return $objects;

    } catch (S3Exception $e) {
      $this->errorstack->addError('BUCKET', 'S3_EXCEPTION', $e->getMessage());
    }
    return array();
  }

  /**
   * @inheritDoc
   */
  public function dirAvailable(string $directory): bool
  {
    /**
     * NOTE: we may have to check for objects in this bucket path
     * as s3 is a flat file system
     */

     try{
       /**
        * @see http://stackoverflow.com/questions/18683206/list-objects-in-a-specific-folder-on-amazon-s3
        */

       $response = $this->client->listObjectsV2([
         "Bucket" => $this->bucket,
         "Prefix" => $this->getPrefixedPath($directory),
         "Delimiter" => '/',
         "MaxKeys" => 1
       ]);

       $result = $response->get('Contents') ?? false;

       //
       // NOTE: we limit "MaxKeys" to 1
       // but I expect some S3 APIs to ignore that
       // we MIGHT change that to a > 1 later.
       //
       if($result && count($result) === 1) {
         return true;
       } else {
         return false;
       }
     } catch(S3Exception $e) {
       $this->errorstack->addError('BUCKET', 'S3_EXCEPTION', $e->getMessage());
     }

    return false;
  }

  /**
   * @inheritDoc
   */
  public function isFile(string $remotefile): bool
  {
    // workaround?
    // path prefixing by overwriting var value
    $remotefile = $this->getPrefixedPath($remotefile);
    if(strpos($remotefile, '/') === 0) {
      $remotefile = substr($remotefile, 1, strlen($remotefile)-1);
    }
    return $this->objectExists($remotefile) && substr($remotefile, strlen($remotefile)-1, 1) !== '/';
  }

  /**
   * this is the official AWS public grant URI
   * for determining public-read ACL using getAccessInfo/getObjectAcl
   * @var string
   */
  const PUBLIC_GRANT_URI = 'http://acs.amazonaws.com/groups/global/AllUsers';

  /**
   * gets the object access parameters
   * currently, only private (default) and public-read are returned
   * @param string $remotefile
   * @return string [class-defined ACL]
   */
  public function getAccessInfo(string $remotefile) : string {
    // default/fallback
    $access = self::ACL_PRIVATE;
    try{
      /**
       * @see http://docs.aws.amazon.com/cli/latest/reference/s3api/get-object-acl.html
       * @see https://github.com/thephpleague/flysystem-aws-s3-v3/blob/master/src/AwsS3Adapter.php
       */
      $result = $this->client->getObjectAcl([
       'Bucket' => $this->bucket,
       'Key'    => $this->getPrefixedPath($remotefile),
      ]);
      foreach ($result['Grants'] as $grant) {
        if (
          isset($grant['Grantee']['URI'])
          && $grant['Grantee']['URI'] === self::PUBLIC_GRANT_URI
          && $grant['Permission'] === 'READ'
        ) {
          $access = self::ACL_PUBLIC_READ;
          break;
        }
       }
    } catch (S3Exception $e) {
      $this->errorstack->addError('BUCKET', 'S3_EXCEPTION', $e->getMessage());
    }
    return $access;
  }

}
