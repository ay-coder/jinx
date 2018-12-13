<?php
namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Http\Transformers\UserNotificationsTransformer;
use App\Http\Controllers\Api\BaseApiController;
use App\Repositories\UserNotifications\EloquentUserNotificationsRepository;

class APIUserNotificationsController extends BaseApiController
{
    /**
     * UserNotifications Transformer
     *
     * @var Object
     */
    protected $usernotificationsTransformer;

    /**
     * Repository
     *
     * @var Object
     */
    protected $repository;

    /**
     * PrimaryKey
     *
     * @var string
     */
    protected $primaryKey = 'usernotificationsId';

    /**
     * __construct
     *
     */
    public function __construct()
    {
        $this->repository                       = new EloquentUserNotificationsRepository();
        $this->usernotificationsTransformer = new UserNotificationsTransformer();
    }

    /**
     * List of All UserNotifications
     *
     * @param Request $request
     * @return json
     */
    public function index(Request $request)
    {
        $userInfo   = $this->getAuthenticatedUser();
        $paginate   = $request->get('paginate') ? $request->get('paginate') : false;
        $orderBy    = $request->get('orderBy') ? $request->get('orderBy') : 'id';
        $order      = $request->get('order') ? $request->get('order') : 'ASC';
        $items      = $this->repository->model->with('user', 'other_user')->where([
            'other_user_id' => $userInfo->id,
            'is_deleted'    => 0
        ])
        ->orderBy('id', 'desc')
        ->get();

        if(isset($items) && count($items))
        {
            $itemsOutput = $this->usernotificationsTransformer->transformCollection($items);

            return $this->successResponse($itemsOutput);
        }

        return $this->setStatusCode(400)->failureResponse([
            'message' => 'No Notifications Found!'
            ], 'No Notifications Found');
    }

    /**
     * Create
     *
     * @param Request $request
     * @return string
     */
    public function create(Request $request)
    {
        $model = $this->repository->create($request->all());

        if($model)
        {
            $responseData = $this->usernotificationsTransformer->transform($model);

            return $this->successResponse($responseData, 'UserNotifications is Created Successfully');
        }

        return $this->setStatusCode(400)->failureResponse([
            'reason' => 'Invalid Inputs'
            ], 'Something went wrong !');
    }

    /**
     * Read All
     *
     * @param Request $request
     * @return string
     */
    public function readAll(Request $request)
    {
        $userInfo = $this->getAuthenticatedUser();
        $status   = $this->repository->model->where([
            'other_user_id' => $userInfo->id,
            'is_read'       => 0
        ])->update([
            'is_read' => 1
        ]);

        if($status)
        {
            $responseData = [
                'message' => 'Messages Read Successfully!'
            ];

            return $this->successResponse($responseData, 'Messages Read Successfully!');
        }

        return $this->setStatusCode(400)->failureResponse([
            'reason' => 'No Unread Messages Found!'
            ], 'No Unread Messages Found!');
    }

    /**
     * View
     *
     * @param Request $request
     * @return string
     */
    public function show(Request $request)
    {
        $itemId = (int) hasher()->decode($request->get($this->primaryKey));

        if($itemId)
        {
            $itemData = $this->repository->getById($itemId);

            if($itemData)
            {
                $responseData = $this->usernotificationsTransformer->transform($itemData);

                return $this->successResponse($responseData, 'View Item');
            }
        }

        return $this->setStatusCode(400)->failureResponse([
            'reason' => 'Invalid Inputs or Item not exists !'
            ], 'Something went wrong !');
    }

    /**
     * Edit
     *
     * @param Request $request
     * @return string
     */
    public function edit(Request $request)
    {
        $itemId = (int) hasher()->decode($request->get($this->primaryKey));

        if($itemId)
        {
            $status = $this->repository->update($itemId, $request->all());

            if($status)
            {
                $itemData       = $this->repository->getById($itemId);
                $responseData   = $this->usernotificationsTransformer->transform($itemData);

                return $this->successResponse($responseData, 'UserNotifications is Edited Successfully');
            }
        }

        return $this->setStatusCode(400)->failureResponse([
            'reason' => 'Invalid Inputs'
        ], 'Something went wrong !');
    }

    /**
     * Delete
     *
     * @param Request $request
     * @return string
     */
    public function delete(Request $request)
    {
        $itemId = (int) hasher()->decode($request->get($this->primaryKey));

        if($itemId)
        {
            $status = $this->repository->destroy($itemId);

            if($status)
            {
                return $this->successResponse([
                    'success' => 'UserNotifications Deleted'
                ], 'UserNotifications is Deleted Successfully');
            }
        }

        return $this->setStatusCode(404)->failureResponse([
            'reason' => 'Invalid Inputs'
        ], 'Something went wrong !');
    }
}