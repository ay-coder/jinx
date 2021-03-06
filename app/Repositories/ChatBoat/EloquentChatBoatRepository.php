<?php namespace App\Repositories\ChatBoat;

/**
 * Class EloquentChatBoatRepository
 *
 * @author Anuj Jaha ( er.anujjaha@gmail.com)
 */

use App\Models\ChatBoat\ChatBoat;
use App\Repositories\DbRepository;
use App\Exceptions\GeneralException;

class EloquentChatBoatRepository extends DbRepository
{
    /**
     * ChatBoat Model
     *
     * @var Object
     */
    public $model;

    /**
     * ChatBoat Title
     *
     * @var string
     */
    public $moduleTitle = 'ChatBoat';

    /**
     * Table Headers
     *
     * @var array
     */
    public $tableHeaders = [
        'id'        => 'Id',
'user_id'        => 'User_id',
'other_user_id'        => 'Other_user_id',
'question'        => 'Question',
'user_answer'        => 'User_answer',
'other_user_answer'        => 'Other_user_answer',
'accept_user_id'        => 'Accept_user_id',
'accept_other_user_id'        => 'Accept_other_user_id',
'user_answer_time'        => 'User_answer_time',
'other_user_answer_time'        => 'Other_user_answer_time',
'created_at'        => 'Created_at',
'updated_at'        => 'Updated_at',
"actions"         => "Actions"
    ];

    /**
     * Table Columns
     *
     * @var array
     */
    public $tableColumns = [
        'id' =>   [
                'data'          => 'id',
                'name'          => 'id',
                'searchable'    => true,
                'sortable'      => true
            ],
		'user_id' =>   [
                'data'          => 'user_id',
                'name'          => 'user_id',
                'searchable'    => true,
                'sortable'      => true
            ],
		'other_user_id' =>   [
                'data'          => 'other_user_id',
                'name'          => 'other_user_id',
                'searchable'    => true,
                'sortable'      => true
            ],
		'question' =>   [
                'data'          => 'question',
                'name'          => 'question',
                'searchable'    => true,
                'sortable'      => true
            ],
		'user_answer' =>   [
                'data'          => 'user_answer',
                'name'          => 'user_answer',
                'searchable'    => true,
                'sortable'      => true
            ],
		'other_user_answer' =>   [
                'data'          => 'other_user_answer',
                'name'          => 'other_user_answer',
                'searchable'    => true,
                'sortable'      => true
            ],
		'accept_user_id' =>   [
                'data'          => 'accept_user_id',
                'name'          => 'accept_user_id',
                'searchable'    => true,
                'sortable'      => true
            ],
		'accept_other_user_id' =>   [
                'data'          => 'accept_other_user_id',
                'name'          => 'accept_other_user_id',
                'searchable'    => true,
                'sortable'      => true
            ],
		'user_answer_time' =>   [
                'data'          => 'user_answer_time',
                'name'          => 'user_answer_time',
                'searchable'    => true,
                'sortable'      => true
            ],
		'other_user_answer_time' =>   [
                'data'          => 'other_user_answer_time',
                'name'          => 'other_user_answer_time',
                'searchable'    => true,
                'sortable'      => true
            ],
		'created_at' =>   [
                'data'          => 'created_at',
                'name'          => 'created_at',
                'searchable'    => true,
                'sortable'      => true
            ],
		'updated_at' =>   [
                'data'          => 'updated_at',
                'name'          => 'updated_at',
                'searchable'    => true,
                'sortable'      => true
            ],
		'actions' => [
            'data'          => 'actions',
            'name'          => 'actions',
            'searchable'    => false,
            'sortable'      => false
        ]
    ];

    /**
     * Is Admin
     *
     * @var boolean
     */
    protected $isAdmin = false;

    /**
     * Admin Route Prefix
     *
     * @var string
     */
    public $adminRoutePrefix = 'admin';

    /**
     * Client Route Prefix
     *
     * @var string
     */
    public $clientRoutePrefix = 'frontend';

    /**
     * Admin View Prefix
     *
     * @var string
     */
    public $adminViewPrefix = 'backend';

    /**
     * Client View Prefix
     *
     * @var string
     */
    public $clientViewPrefix = 'frontend';

    /**
     * Module Routes
     *
     * @var array
     */
    public $moduleRoutes = [
        'listRoute'     => 'chatboat.index',
        'createRoute'   => 'chatboat.create',
        'storeRoute'    => 'chatboat.store',
        'editRoute'     => 'chatboat.edit',
        'updateRoute'   => 'chatboat.update',
        'deleteRoute'   => 'chatboat.destroy',
        'dataRoute'     => 'chatboat.get-list-data'
    ];

    /**
     * Module Views
     *
     * @var array
     */
    public $moduleViews = [
        'listView'      => 'chatboat.index',
        'createView'    => 'chatboat.create',
        'editView'      => 'chatboat.edit',
        'deleteView'    => 'chatboat.destroy',
    ];

    /**
     * Construct
     *
     */
    public function __construct()
    {
        $this->model = new ChatBoat;
    }

    /**
     * Create ChatBoat
     *
     * @param array $input
     * @return mixed
     */
    public function create($input)
    {
        $input = $this->prepareInputData($input, true);
        $model = $this->model->create($input);

        if($model)
        {
            return $model;
        }

        return false;
    }

    /**
     * Update ChatBoat
     *
     * @param int $id
     * @param array $input
     * @return bool|int|mixed
     */
    public function update($id, $input)
    {
        $model = $this->model->find($id);

        if($model)
        {
            $input = $this->prepareInputData($input);

            return $model->update($input);
        }

        return false;
    }

    /**
     * Destroy ChatBoat
     *
     * @param int $id
     * @return mixed
     * @throws GeneralException
     */
    public function destroy($id)
    {
        $model = $this->model->find($id);

        if($model)
        {
            return $model->delete();
        }

        return  false;
    }

    /**
     * Get All
     *
     * @param string $orderBy
     * @param string $sort
     * @return mixed
     */
    public function getAll($orderBy = 'id', $sort = 'asc')
    {
        return $this->model->orderBy($orderBy, $sort)->get();
    }

    /**
     * Get by Id
     *
     * @param int $id
     * @return mixed
     */
    public function getById($id = null)
    {
        if($id)
        {
            return $this->model->find($id);
        }

        return false;
    }

    /**
     * Get Table Fields
     *
     * @return array
     */
    public function getTableFields()
    {
        return [
            $this->model->getTable().'.*'
        ];
    }

    /**
     * @return mixed
     */
    public function getForDataTable()
    {
        return $this->model->select($this->getTableFields())->get();
    }

    /**
     * Set Admin
     *
     * @param boolean $isAdmin [description]
     */
    public function setAdmin($isAdmin = false)
    {
        $this->isAdmin = $isAdmin;

        return $this;
    }

    /**
     * Prepare Input Data
     *
     * @param array $input
     * @param bool $isCreate
     * @return array
     */
    public function prepareInputData($input = array(), $isCreate = false)
    {
        if($isCreate)
        {
            $input = array_merge($input, ['user_id' => access()->user()->id]);
        }

        return $input;
    }

    /**
     * Get Table Headers
     *
     * @return string
     */
    public function getTableHeaders()
    {
        if($this->isAdmin)
        {
            return json_encode($this->setTableStructure($this->tableHeaders));
        }

        $clientHeaders = $this->tableHeaders;

        unset($clientHeaders['username']);

        return json_encode($this->setTableStructure($clientHeaders));
    }

    /**
     * Get Table Columns
     *
     * @return string
     */
    public function getTableColumns()
    {
        if($this->isAdmin)
        {
            return json_encode($this->setTableStructure($this->tableColumns));
        }

        $clientColumns = $this->tableColumns;

        unset($clientColumns['username']);

        return json_encode($this->setTableStructure($clientColumns));
    }
}