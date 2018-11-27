<?php
namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Http\Transformers\ChatBoatTransformer;
use App\Http\Controllers\Api\BaseApiController;
use App\Repositories\ChatBoat\EloquentChatBoatRepository;
use App\Models\Messages\Messages;

class APIChatBoatController extends BaseApiController
{
    /**
     * ChatBoat Transformer
     *
     * @var Object
     */
    protected $chatboatTransformer;

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
    protected $primaryKey = 'chatboatId';

    /**
     * __construct
     *
     */
    public function __construct()
    {
        $this->repository                       = new EloquentChatBoatRepository();
        $this->chatboatTransformer = new ChatBoatTransformer();
    }

    /**
     * List of All ChatBoat
     *
     * @param Request $request
     * @return json
     */
    public function index(Request $request)
    {
        $userInfo   = $this->getAuthenticatedUser();
        $chatBoats  = $this->repository->model->with(['user', 'other_user'])->where([
            'user_id'           => $userInfo->id,
            'accept_user_id'    => 0 
        ])->orWhere([
            'other_user_id'           => $userInfo->id,
            'accept_other_user_id'    => 0 
        ])->get();

        if(isset($chatBoats) && count($chatBoats))
        {
            $itemsOutput = $this->chatboatTransformer->transformChatBoat($chatBoats);

            return $this->successResponse($itemsOutput);
        }

        return $this->setStatusCode(400)->failureResponse([
            'message' => 'Unable to find ChatBoat!'
            ], 'No ChatBoat Found !');
    }

    /**
     * List of All ChatBoat
     *
     * @param Request $request
     * @return json
     */
    public function answer(Request $request)
    {
        if($request->has('chat_boat_id') && $request->has('answer'))
        {
            $userInfo   = $this->getAuthenticatedUser();
            $chatBoat   = $this->repository->model->where('id', $request->get('chat_boat_id'))->first();

            if($chatBoat->user_id == $userInfo->id)
            {
                $chatBoat->user_answer      = $request->get('answer');
                $chatBoat->accept_user_id   = $userInfo->id;
                $chatBoat->user_answer_time = date('Y-m-d H:i:s');
                $chatBoat->save();
            }
            
            if($chatBoat->other_user_id == $userInfo->id)
            {
                $chatBoat->other_user_answer      = $request->get('answer');
                $chatBoat->accept_other_user_id   = $userInfo->id;
                $chatBoat->other_user_answer_time = date('Y-m-d H:i:s');
                $chatBoat->save();
            }

            if($chatBoat->accept_user_id && $chatBoat->accept_other_user_id)
            {
                $chatBoat->is_ready = 1;    
                $chatBoat->save();    

                $message = Messages::create([
                    'user_id'       => $chatBoat->user_id,
                    'other_user_id' => $chatBoat->other_user_id,
                    'message'       => "Let's Talk"
                ]);
            }
            return $this->successResponse([
                'success' => 'Chatboat Saved Successfully'
            ], 'Chatboat Saved Successfully'); 
        }
        
        return $this->setStatusCode(200)->failureResponse([
            'message' => 'Invalid Chat Boat or No Chat Boat Found!'
            ], 'Invalid Chat Boat or No Chat Boat Found!');
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
            $responseData = $this->chatboatTransformer->transform($model);

            return $this->successResponse($responseData, 'ChatBoat is Created Successfully');
        }

        return $this->setStatusCode(400)->failureResponse([
            'reason' => 'Invalid Inputs'
            ], 'Something went wrong !');
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
                $responseData = $this->chatboatTransformer->transform($itemData);

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
                $responseData   = $this->chatboatTransformer->transform($itemData);

                return $this->successResponse($responseData, 'ChatBoat is Edited Successfully');
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
                    'success' => 'ChatBoat Deleted'
                ], 'ChatBoat is Deleted Successfully');
            }
        }

        return $this->setStatusCode(404)->failureResponse([
            'reason' => 'Invalid Inputs'
        ], 'Something went wrong !');
    }
}