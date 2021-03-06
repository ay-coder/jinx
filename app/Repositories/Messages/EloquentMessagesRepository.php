<?php namespace App\Repositories\Messages;

/**
 * Class EloquentMessagesRepository
 *
 * @author Anuj Jaha ( er.anujjaha@gmail.com)
 */

use App\Models\Messages\Messages;
use App\Repositories\DbRepository;
use App\Exceptions\GeneralException;
use App\Models\ChatBoat\ChatBoat;
use App\Models\AdminMessages\AdminMessages;

class EloquentMessagesRepository extends DbRepository
{
    /**
     * Messages Model
     *
     * @var Object
     */
    public $model;

    /**
     * Messages Title
     *
     * @var string
     */
    public $moduleTitle = 'Messages';

    /**
     * Table Headers
     *
     * @var array
     */
    public $tableHeaders = [
        'id'        => 'Id',
'user_id'        => 'User_id',
'other_user_id'        => 'Other_user_id',
'message'        => 'Message',
'is_read'        => 'Is_read',
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
		'message' =>   [
                'data'          => 'message',
                'name'          => 'message',
                'searchable'    => true,
                'sortable'      => true
            ],
		'is_read' =>   [
                'data'          => 'is_read',
                'name'          => 'is_read',
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
        'listRoute'     => 'messages.index',
        'createRoute'   => 'messages.create',
        'storeRoute'    => 'messages.store',
        'editRoute'     => 'messages.edit',
        'updateRoute'   => 'messages.update',
        'deleteRoute'   => 'messages.destroy',
        'dataRoute'     => 'messages.get-list-data'
    ];

    /**
     * Module Views
     *
     * @var array
     */
    public $moduleViews = [
        'listView'      => 'messages.index',
        'createView'    => 'messages.create',
        'editView'      => 'messages.edit',
        'deleteView'    => 'messages.destroy',
    ];

    /**
     * Construct
     *
     */
    public function __construct()
    {
        $this->model = new Messages;
    }

    /**
     * Create Messages
     *
     * @param array $input
     * @return mixed
     */
    public function create($input)
    {
        
    }

    /**
     * Update Messages
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
     * Destroy Messages
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


    
    /**
     * Get All User Messages
     * 
     * @var int
     */
    public function getAllUserMessages($userId = null)
    {
        if($userId)
        {
           /* $chatBoat = ChatBoat::where([
                'is_ready' => 1
            ])->where(function($q) use($userId)
            {
                $q->where('accept_user_id', $userId)
                ->orWhere('accept_other_user_id', $userId);
            })
            ->get();

            $senderIds      = $chatBoat->pluck('user_id')->toArray();
            $receiverIds    = $chatBoat->pluck('other_user_id')->toArray();
            $allowedUserIds = array_unique(array_merge($senderIds, $receiverIds));*/

            /**
             *   Chat Boat
             * where(function($q) use($allowedUserIds)
            {
                $q->whereIn('user_id', $allowedUserIds)->whereIn('other_user_id', $allowedUserIds);
            })
             *
             */
            $messages = $this->model
            /*whereIn('user_id', $allowedUserIds)
            ->orWhereIn('other_user_id', $allowedUserIds)*/
            ->with([
                'user',
                'other_user'
            ])
            ->orderBy('id', 'desc')
            ->get();

            $response   = [];
            $userIds    = [];
            $inPair     = [];
            $outPair    = [];
            $messageIds = [];

            foreach($messages as $message)
            {
                $checkInPair = $message->user_id . ','. $message->other_user_id;
                $checkOutPair = $message->other_user_id . ','. $message->user_id;

                if(!in_array($checkInPair, $inPair) && !in_array($checkOutPair, $outPair) && !in_array($checkOutPair, $inPair) && !in_array($checkInPair, $outPair) )
                {
                    $messageIds[]   = $message->id;
                    $response[]     = $message;
                    $inPair[]       = $checkInPair;
                    $outPair[]      = $checkOutPair;
                }
            }
            
            AdminMessages::whereIn('message_id', $messageIds)->delete();
            
            return $response;
        }
        
        return false;
    }

    /**
     * Get All
     *
     * @param string $orderBy
     * @param string $sort
     * @return mixed
     */
    public function getAllChat($userId = null, $otherUserId = null)
    {
        if($userId && $otherUserId)
        {
            $currentUserId      = access()->user()->id;
            $getUserSkippIds    = access()->getUserHiddenMessageIds($currentUserId);

            return $this->model->whereNotIn('id', $getUserSkippIds)
            ->where([
                'user_id'   => $userId,
                'other_user_id'    => $otherUserId
            ])->orWhere([
                'other_user_id'   => $userId,
                'user_id'    => $otherUserId
            ])
            ->with([
                'user',
                'other_user'
            ])
            ->get();
        }

        return false;
    }
    
}