<?php
/**
 * Auther aoyagikouhei
 * 2011/07/05 ver 1.0
 *
 * Install
 * Extract the release file under protected/extensions
 * 
 * In config/main.php:
  'session'=>array(
      'class'=>'ext.EMongoDbHttpSession',
    ),
 *
 * options
 * connectionString : host:port          : defalut localhost:27017
 * dbName           : database name      : default test
 * collectionName   : collaction name    : default yiisession
 * idColumn         : id column name     : default id
 * dataColumn       : data column name   : default dada
 * expireColumn     : expire column name : default expire
 *
 * example
   'session'=>array(
      'class'=>'EMongoDbHttpSession',
      'connectionString' => 'localhost:27017',
      'dbName' => 'test',
      'collectionName' => 'yiilog',
      'idColumn' => 'id',
      'dataColumn' => 'data',
      'expireColumn' => 'expire',
      ),
    ),
 *
 */
class EMongoDbHttpSession extends CHttpSession
{
  /**
   * @var string Mongo Db host + port
   */
  public $connectionString="localhost:27017";

  /**
   * @var string Mongo Db Name
   */
  public $dbName="test";
  
  /**
   * @var string Collection name
   */
  public $collectionName="yiisession";

  /**
   * @var string id column name
   */
  public $idColumn = 'id';

  /**
   * @var string level data name
   */
  public $dataColumn="data";

  /**
   * @var string expire column name
   */
  public $expireColumn="expire";

  /**
  /**
   * @var Mongo mongo Db collection
   */
  private $collection;
   
  /**
   * Initializes the route.
   * This method is invoked after the route is created by the route manager.
   */
  public function init()
  {
    $connection = new Mongo($this->connectionString);
    $dbName = $this->dbName;
    $collectionName = $this->collectionName;
    $this->collection = $connection->$dbName->$collectionName;
    parent::init();
  }
  
  protected function getData($id) {
    return $this->collection->findOne(array($this->idColumn => $id), array($this->dataColumn));
  }
  
  protected function getExipireTime() {
  	return time() + $this->getTimeout();
  }

  /**
   * Returns a value indicating whether to use custom session storage.
   * This method overrides the parent implementation and always returns true.
   * @return boolean whether to use custom storage.
   */
  public function getUseCustomStorage()
  {
    return true;
  }
  
  /**
   * Session open handler.
   * Do not call this method directly.
   * @param string $savePath session save path
   * @param string $sessionName session name
   * @return boolean whether session is opened successfully
   */
  public function openSession($savePath,$sessionName)
  {
    $this->gcSession(0);
  }
  
  /**
  * Session read handler.
  * Do not call this method directly.
  * @param string $id session ID
  * @return string the session data
  */
  public function readSession($id)
  {
    $row = $this->getData($id);
    return is_null($row) ? '' : $row[$this->dataColumn];
  }
  
  /**
  * Session write handler.
  * Do not call this method directly.
  * @param string $id session ID
  * @param string $data session data
  * @return boolean whether session write is successful
  */
  public function writeSession($id,$data)
  {
    return $this->collection->update(
      array($this->idColumn => $id)
      ,array(
        $this->dataColumn => $data
        ,$this->expireColumn => $this->getExipireTime()
        ,$this->idColumn => $id
      )
      ,array('upsert' => true)
    );
  }
  
  /**
  * Session destroy handler.
  * Do not call this method directly.
  * @param string $id session ID
  * @return boolean whether session is destroyed successfully
  */
  public function destroySession($id)
  {
    return $this->collection->remove(array($this->idColumn => id));
  }
  
  /**
  * Session GC (garbage collection) handler.
  * Do not call this method directly.
  * @param integer $maxLifetime the number of seconds after which data will be seen as 'garbage' and cleaned up.
  * @return boolean whether session is GCed successfully
  */
  public function gcSession($maxLifetime)
  {
    return $this->collection->remove(array($this->expireColumn => array('$lt' => time())));
  }
  
  /**
  * Updates the current session id with a newly generated one .
  * Please refer to {@link http://php.net/session_regenerate_id} for more details.
  * @param boolean $deleteOldSession Whether to delete the old associated session file or not.
  * @since 1.1.8
  */
  public function regenerateID($deleteOldSession=false)
  {
    $oldId = session_id();;
    parent::regenerateID(false);
    $newId = session_id();
    $row = $this->getData($oldId);
    if (is_null($row)) {
      $this->collection->insert(array(
        $this->idColumn => $newId
        ,$this->expireColumn => $this->getExipireTime()
      ));
    } else if ($deleteOldSession) {
      $this->collection->update(
        array($this->idColumn => $oldId)
        ,array($this->idColumn => $newId)
      );
    } else {
      $row[$this->idColumn] = $newId;
      unset($row['_id']);
      $this->collection->insert($row);
    }
  }
}
