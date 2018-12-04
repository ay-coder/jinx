<?php
namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Http\Transformers\UserInterestsTransformer;
use App\Http\Controllers\Api\BaseApiController;
use App\Repositories\UserInterests\EloquentUserInterestsRepository;
use App\Models\UserInterests\UserInterests;
use App\Models\ChatBoat\ChatBoat;
use App\Models\Messages\Messages;

class APIUserInterestsController extends BaseApiController
{
    /**
     * UserInterests Transformer
     *
     * @var Object
     */
    protected $userinterestsTransformer;

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
    protected $primaryKey = 'userinterestsId';

    /**
     * __construct
     *
     */
    public function __construct()
    {
        $this->repository                       = new EloquentUserInterestsRepository();
        $this->userinterestsTransformer = new UserInterestsTransformer();
    }

    /**
     * List of All UserInterests
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
            $itemsOutput = $this->userinterestsTransformer->transformCollection($items);

            return $this->successResponse($itemsOutput);
        }

        return $this->setStatusCode(400)->failureResponse([
            'message' => 'Unable to find UserInterests!'
            ], 'No UserInterests Found !');
    }

    /**
     * Create
     *
     * @param Request $request
     * @return string
     */
    public function create(Request $request)
    {
        if($request->has('interested_user_id'))
        {
            $userInfo   = $this->getAuthenticatedUser();
            $isExists   = UserInterests::where([
                'user_id' => $userInfo->id,
                'interested_user_id' => $request->get('interested_user_id')
            ])->first();

            if(isset($isExists))
            {
                return $this->setStatusCode(400)->failureResponse([
                    'reason' => 'Already shown Interest'
                    ], 'Already shown Interest');
            }

            $data       = [
                'user_id'               => $userInfo->id,
                'interested_user_id'    => $request->get('interested_user_id'),
                'description'           => 'Show Interest at ' . date('Y-m-d H:i:s')
            ];

            $model = $this->repository->create($data);

            if($model)
            {
                $userInterest   = UserInterests::where([
                    'user_id'            => $userInfo->id,
                    'interested_user_id' => $request->get('interested_user_id')
                ])->first();

                $otherUserInterest   = UserInterests::where([
                    'user_id'            => $request->get('interested_user_id'),
                    'interested_user_id' => $userInfo->id
                ])->first();

                if($userInterest && $otherUserInterest)
                {
                    Messages::create([
                        'user_id'       => $userInfo->id,
                        'other_user_id' => $request->get('interested_user_id'),
                        'message'       => "Let's Talk "
                    ]);

                    //$question = access()->getRandomQuestion();
                    /* $chatBoat = ChatBoat::create([
                        'user_id'       => $userInfo->id,
                        'other_user_id' => $request->get('interested_user_id'),
                        'question'      => $question['question'],
                    ]);*/
                }

                $responseData = [
                    'message' => 'User Interest Created Successfully'
                ];

                return $this->successResponse($responseData);
            }
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
                $responseData = $this->userinterestsTransformer->transform($itemData);

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
                $responseData   = $this->userinterestsTransformer->transform($itemData);

                return $this->successResponse($responseData, 'UserInterests is Edited Successfully');
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
        if($request->has('user_id'))
        {
            $userInfo   = $this->getAuthenticatedUser();
            $isExists   = UserInterests::where([
                'user_id'            => $userInfo->id,
                'interested_user_id' => $request->get('user_id')
            ])
            ->orWhere([
                'user_id'            => $request->get('user_id'),
                'interested_user_id' => $userInfo->id
            ])
            ->first();

            if(isset($isExists))
            {
                $isExists->delete();   

                return $this->successResponse([
                    'success' => 'Decline Chat Request Successfully'
                ], 'Decline Chat Request Successfully');
            }
        }

        return $this->setStatusCode(404)->failureResponse([
            'reason' => 'No Chat Request Found !'
        ], 'No Chat Request Found !');
    }
}