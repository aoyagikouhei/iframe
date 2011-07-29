<?php
/**
 * EMongoDbLogViewer.php
 *
 * A widget to show and filter the log records generated with EMongoDbLogRoute
 *
 * Requirements:
 *
 * Extension EMongoDbLogRoute from aoyagikouhei
 * @link http://www.yiiframework.com/extension/mongodblogroute/
 *
 * Usage:
 * - Install the extension EMongoDbLogRoute
 *
 * - Create a controller action and add the lines below.
 *   No need to generate an extra view to render the widget
 *

 function actionShowLog()
 {
	 //capture the widget output
	 $output = $this->widget('ext.mongodblogviewer.EMongoDbLogViewer',array(),true);

	 //render the widget into the controllers layout
	 $this->renderText($output);
 }

 *
 * @author Joe Blocher <yii@myticket.at>
 * @copyright 2011 myticket it-solutions gmbh
 * @license New BSD License
 * @category User Interface
 * @version 1.1
 */

class EMongoDbLogViewer extends CWidget {
    /**
     *
     * @var array additional config for the dataprovider
     */
    public $dataproviderConfig = array();

    /**
     *
     * @var array the available items for pageSize settings
     */
    public $pageSizes = array(25, 50, 100, 200, 500);

    /**
     *
     * @var string the view to render
     */
    public $view = 'view';

    /**
     *
     * @var EMongoDbLogRoute the instance of the EMongoDbLogRoute component
     */
    protected static $_logRoute;

    /**
     *
     * @var MongoDB the instance of the EMongoDbLogRoute db
     */
    protected static $_db;

    /**
     *
     * @var MongoCollection the instance of the EMongoDbLogRoute collection
     */
    protected static $_collection;

    /**
     * Get the configured db from EMongoDbLogRoute
     *
     * @return Mongo
     */
    public function getDb()
    {
        if (self::$_db === null) {
            $connection = new Mongo($this->logRoute->connectionString);
            self::$_db = $connection->selectDB($this->logRoute->dbName);
        }

        return self::$_db;
    }

    /**
     * Get the configured collection from EMongoDbLogRoute
     *
     * @return MongoCollection
     */
    public function getCollection()
    {
        if (self::$_collection === null)
            self::$_collection = $this->db->selectCollection($this->logRoute->collectionName);

        return self::$_collection;
    }

    /**
     * Get the EMongoDbLogRoute component
     *
     * @return EMongoDbLogRoute
     */
    public function getLogRoute()
    {
        if (self::$_logRoute !== null)
            return self::$_logRoute;

        $logRoutes = Yii::app()->log->routes;

        foreach ($logRoutes as $idx => $route)
        {
            if ($route instanceof EMongoDbLogRoute)
            {
                self::$_logRoute = $route;
                return self::$_logRoute;
            }
        }

        throw new CException('EMongoDbLogRoute is not installed');
    }

    /**
     * Distinct query for a specific attribute in the collection
     *
     * @param string $attribute
     * @param array $criteria additional criteria
     * @return array
     */
    public function findDistinct($attribute, $criteria = array())
    {
        $command = array('distinct' => $this->logRoute->collectionName, 'key' => $attribute);

        if (!empty($criteria))
            array_push($command, $criteria);

        $result = $this->db->command($command);
        return $result['ok'] = 1 ? $result['values'] : null;
    }


    /**
     * Get the sorted dropdown listdata of a specific attribute
     *
     * @param string $attribute
     * @return array
     */
    protected function getDropDownItems($attribute)
    {
        $data = array();

        $items = $this->findDistinct($attribute);
        if (!empty($items)) {
            foreach ($items as $item)
            $data[$item] = $item;
        }

        ksort($data); //sort by item
        return $data;
    }

    /**
     * Generate the searchform
     * Use GET because of pagination
     *
     * @return CForm
     */
    public function getSearchForm()
    {
        $pageSizes = array();

    	$url = $this->controller->createUrl('');

        foreach ($this->pageSizes as $size)
           $pageSizes[$size] = $size;

        $config = array(
            'title' => 'Filter',
            'method' => 'POST', //use GET because of pagination

            'elements' => array(
                'level' => array(
                    'type' => 'dropdownlist',
                    'items' => $this->getDropDownItems('level'),
                    'prompt' => ' - ',
                    ),
                'category' => array(
                    'type' => 'dropdownlist',
                    'items' => $this->getDropDownItems('category'),
                    'prompt' => ' - ',
                    ),

                'keywords' => array(
                    'type' => 'text',
                    'size' => 60,
                    'maxlength' => 100,
                    ),

                'pagesize' => array(
                    'type' => 'dropdownlist',
                    'items' => $pageSizes,
                    ),

                ),

            'buttons' => array(
                'apply' => array(
                    'type' => 'submit',
                    'label' => 'Apply',
                    ),
            	'reset' => array(
            	    'type' => 'reset',
            	    'label' => 'Reset',
            	    ),
		        ),
            );

        $formModel = new EMongoDBLogSearchModel;

        if (isset($_POST['EMongoDBLogSearchModel']))
            $formModel->attributes = $_POST['EMongoDBLogSearchModel'];

        return new CForm($config, $formModel);
    }

    /**
     * Get the criteria for the mongoDB query
     * Check the submitted values from the searchform
     * Use GET because of pagination
     *
     * @return array
     */
    protected function getSearchCriteria()
    {
        $criteria = array();

        if (isset($_POST['EMongoDBLogSearchModel']))
        {
            $attributes = $_POST['EMongoDBLogSearchModel'];
            // add level criteria
            if (!empty($attributes['level']))
                $criteria['level'] = $attributes['level'];
            // add category criteria
            if (!empty($attributes['category']))
                $criteria['category'] = $attributes['category'];
            // add keywords criteria
            if (!empty($attributes['keywords']))
            {
                // split words
                $words = preg_split("/[\s,]*\\\"([^\\\"]+)\\\"[\s,]*|" . "[\s,]*'([^']+)'[\s,]*|" . "[\s,]+/",
                    $attributes['keywords'], 0, PREG_SPLIT_NO_EMPTY | PREG_SPLIT_DELIM_CAPTURE);

                if (!empty($words))
                {
                    $orArray = array();
                    // search in this attributes
                    $searchAttributes = array('level', 'category', 'message');

                    foreach ($words as $word) {
                        // regex for containing
                        $regexObj = new MongoRegex('/' . $word . '/i');

                        foreach ($searchAttributes as $attribute)
                        $orArray[] = array($attribute => $regexObj);
                    }

                    if (!empty($orArray))
                        $criteria['$or'] = $orArray;
                }
            }
        }

        return $criteria;
    }

    /**
     * Execute the widget
     */
    public function run()
    {
    	if (Yii::app()->request->isAjaxRequest && isset($_GET['clearlog']))
    	{
			$this->collection->drop();

    		//needs workaround: When to use capped collection
    		// recreate as capped collection
    		$this->db->createCollection(
		    			$this->logRoute->collectionName,
		    			true,
		    			$this->logRoute->collectionSize,
		    			$this->logRoute->collectionMax
    			);


    		echo CHtml::tag('div',array('class'=>'removed','style'=>'margin-top: 1em; padding: 0.5em;'),
    			            'Collection cleared and recreated as capped collection.');
    		Yii::app()->end();
    	}

    	//request by the pager, convert GET to POST
    	if (!isset($_POST['EMongoDBLogSearchModel']))
    	{
    		if(isset($_GET['level']))
    		$_POST['EMongoDBLogSearchModel']['level'] = $_GET['level'];

	    	if(isset($_GET['category']))
	    		$_POST['EMongoDBLogSearchModel']['category'] = $_GET['category'];

	    	if(isset($_GET['keywords']))
	    		$_POST['EMongoDBLogSearchModel']['keywords'] = $_GET['keywords'];

	    	if(isset($_GET['pagesize']))
	    		$_POST['EMongoDBLogSearchModel']['pagesize'] = $_GET['pagesize'];
    	}
    	else //request by searchform
    	{
    		//add the search params to the pagination
			$this->dataproviderConfig['pagination']['params'] = $_POST['EMongoDBLogSearchModel'];
    		$this->dataproviderConfig['pagination']['currentPage'] = 0; //set to first page
    	}

    	//find records
		$criteria = $this->getSearchCriteria();
        $cursor = $this->collection->find($criteria);

        //assign the pageSize
    	if (!empty($_POST['EMongoDBLogSearchModel']['pagesize']))
    		$this->dataproviderConfig['pagination']['pageSize'] = $_POST['EMongoDBLogSearchModel']['pagesize'];
    	else
            $this->dataproviderConfig['pagination']['pageSize'] = $this->pageSizes[0]; //first item as default

        $dataProvider = !empty($cursor)
				        ? new EMongoDbLogDataProvider($cursor, $this->dataproviderConfig)
				        : new CArrayDataProvider(array()); //a dataprovider with no data

        $this->render($this->view, array(
                'dataProvider' => $dataProvider,
                'searchForm' => $this->getSearchForm())
            );
    }


}

/**
 * The model for the searchform
 */
class EMongoDBLogSearchModel extends CFormModel
{
    public $level;
    public $category;
    public $keywords;
    public $pagesize;

    /**
     * Returns the static model of the specified AR class.
     *
     * @return the static model class
     */
    public static function model($className = __CLASS__)
    {
        return new $className;
    }

    /**
     * Declares the validation rules.
     */
    public function rules()
    {
        return array(
            array('level,category,keywords', 'length', 'max' => 100),
            array('pagesize', 'numerical', 'integerOnly' => true, 'min' => 1),
            );
    }

    /**
     * Declares attribute labels.
     */
    public function attributeLabels()
    {
        return array(
            'level' => 'Level',
            'category' => 'Category',
            'keywords' => 'Keywords',
            'pagesize' => 'Items per page',
            );
    }
}

/**
 * The dataProvider for rendering the view
 * No support for custom sorting
 */
class EMongoDbLogDataProvider extends CDataProvider
{
    private $_cursor;

    /**
     * Assign the cursor and config
     *
     * @param MongoCursor $cursor
     * @param array $config
     */
    public function __construct($cursor, $config = array())
    {
        $this->_cursor = $cursor;
        foreach($config as $key => $value)
        $this->$key = $value;
    }

    /**
     * Fetches the data from the collection
     * Uses the assigned cursor
     *
     * @return array list of data items
     */
    protected function fetchData()
    {
        $data = array();

        if (empty($this->_cursor))
            return $data;
        // always sort descending
        $this->_cursor->sort(array('$natural' => - 1));

        if (($pagination = $this->getPagination()) !== false) {
            $pagination->setItemCount($this->getTotalItemCount());

            $limit = $pagination->pageSize;
            $skip = $pagination->currentPage * $limit;
            $this->_cursor->limit($limit)->skip($skip);
        }
        // fetch data
        foreach ($this->_cursor as $id => $value)
        $data[] = $value;

        return $data;
    }

    /**
     * Fetches the data item keys from the collection.
     *
     * @return array list of data item keys.
     */
    protected function fetchKeys()
    {
        $keys = array();
        foreach($this->getData() as $i => $data)
        $keys[$i] = $data['_id'];
        return $keys;
    }

    /**
     * Calculates the total number of data items.
     *
     * @return integer the total number of data items.
     */
    protected function calculateTotalItemCount()
    {
        return $this->_cursor->count();
    }
}