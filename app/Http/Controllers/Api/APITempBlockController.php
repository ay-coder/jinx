<?php
namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Http\Transformers\TempBlockTransformer;
use App\Http\Controllers\Api\BaseApiController;
use App\Repositories\TempBlock\EloquentTempBlockRepository;

class APITempBlockController extends BaseApiController
{
    /**
     * TempBlock Transformer
     *
     * @var Object
     */
    protected $tempblockTransformer;

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
    protected $primaryKey = 'tempblockId';

    /**
     * __construct
     *
     */
    public function __construct()
    {
        $this->repository                       = new EloquentTempBlockRepository();
        $this->tempblockTransformer = new TempBlockTransformer();
    }

    /**
     * List of All TempBlock
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
            $itemsOutput = $this->tempblockTransformer->transformCollection($items);

            return $this->successResponse($itemsOutput);
        }

        return $this->setStatusCode(400)->failureResponse([
            'message' => 'Unable to find TempBlock!'
            ], 'No TempBlock Found !');
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
            $userInfo       = $this->getAuthenticatedUser();
            $blockUserId    = $request->get('block_user_id');

            $isExist = $this->repository->model->where([
                'user_id'       => $userInfo->id,
                'block_user_id' => $request->get('block_user_id')
            ])->first();

            if(isset($isExist) && count($isExist))
            {
                return $this->setStatusCode(200)->failureResponse([
                    'reason' => 'Already Blocked!'
                    ], 'Already Blocked!');
            }
            
            $blockData = [
                'user_id'       => $userInfo->id,
                'block_user_id' => $request->get('block_user_id'),
                'description'   => 'Not Interested'
            ];

            $model = $this->repository->create($blockData);

            if($model)
            {
                $responseData = [
                    'message' => 'User Blocked Successfully!'
                ];
                return $this->successResponse($responseData, 'User Blocked Successfully!');
            }
        }

        return $this->setStatusCode(200)->failureResponse([
            'reason' => 'Invalid Block User Id!'
            ], 'Invalid Block User Id!');
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
                $responseData = $this->tempblockTransformer->transform($itemData);

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
                $responseData   = $this->tempblockTransformer->transform($itemData);

                return $this->successResponse($responseData, 'TempBlock is Edited Successfully');
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
                    'success' => 'TempBlock Deleted'
                ], 'TempBlock is Deleted Successfully');
            }
        }

        return $this->setStatusCode(404)->failureResponse([
            'reason' => 'Invalid Inputs'
        ], 'Something went wrong !');
    }
}