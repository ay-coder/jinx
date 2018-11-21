<?php
namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Http\Transformers\BlockUsersTransformer;
use App\Http\Controllers\Api\BaseApiController;
use App\Repositories\BlockUsers\EloquentBlockUsersRepository;

class APIBlockUsersController extends BaseApiController
{
    /**
     * BlockUsers Transformer
     *
     * @var Object
     */
    protected $blockusersTransformer;

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
    protected $primaryKey = 'blockusersId';

    /**
     * __construct
     *
     */
    public function __construct()
    {
        $this->repository                       = new EloquentBlockUsersRepository();
        $this->blockusersTransformer = new BlockUsersTransformer();
    }

    /**
     * List of All BlockUsers
     *
     * @param Request $request
     * @return json
     */
    public function index(Request $request)
    {
        $paginate   = $request->get('paginate') ? $request->get('paginate') : false;
        $orderBy    = $request->get('orderBy') ? $request->get('orderBy') : 'id';
        $order      = $request->get('order') ? $request->get('order') : 'ASC';
        $items      = $paginate ? $this->repository->model->orderBy($orderBy, $order)->paginate($paginate)->items() : $this->repository->getAll($orderBy, $order);

        if(isset($items) && count($items))
        {
            $itemsOutput = $this->blockusersTransformer->transformCollection($items);

            return $this->successResponse($itemsOutput);
        }

        return $this->setStatusCode(400)->failureResponse([
            'message' => 'Unable to find BlockUsers!'
            ], 'No BlockUsers Found !');
    }

    /**
     * Create
     *
     * @param Request $request
     * @return string
     */
    public function create(Request $request)
    {
        if($request->has('block_user_id'))
        {
            $userInfo   = $this->getAuthenticatedUser();
            $isExists   = $this->repository->model->where([
                'user_id'       => $userInfo->id,
                'block_user_id' => $request->get('block_user_id')
            ])->orWhere([
                'user_id'       => $request->get('block_user_id'),
                'block_user_id' => $userInfo->id
            ])->first();

            if(isset($isExists))
            {
                return $this->setStatusCode(200)->failureResponse([
                    'reason' => 'User Already in Block List'
                    ], 'User Already in Block List');
            }

            $status = $this->repository->model->create([
                'user_id'       => $userInfo->id,
                'block_user_id' => $request->get('block_user_id')
            ]);

            if($status)
            {
                return $this->successResponse([
                    'success' => 'User Blocked Successfully'
                ], 'User Blocked Successfully');
            }
        }

        return $this->setStatusCode(200)->failureResponse([
            'reason' => 'No user found'
            ], 'No user found');
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
                $responseData = $this->blockusersTransformer->transform($itemData);

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
                $responseData   = $this->blockusersTransformer->transform($itemData);

                return $this->successResponse($responseData, 'BlockUsers is Edited Successfully');
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
        if($request->has('block_user_id'))
        {
            $userInfo   = $this->getAuthenticatedUser();
            $isExists   = $this->repository->model->where([
                'user_id'       => $userInfo->id,
                'block_user_id' => $request->get('block_user_id')
            ])->first();

            if(isset($isExists))
            {
                $isExists->delete();

                return $this->setStatusCode(200)->failureResponse([
                    'reason' => 'User UnBlocked Successfully'
                    ], 'User UnBlocked Successfully');
            }
        }

        return $this->setStatusCode(404)->failureResponse([
            'reason' => 'No Blocked User Found'
        ], 'No Blocked User Found');
    }
}