<?php
namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Http\Transformers\UserInterestsTransformer;
use App\Http\Controllers\Api\BaseApiController;
use App\Repositories\UserInterests\EloquentUserInterestsRepository;
use App\Models\UserInterests\UserInterests;
use App\Models\ChatBoat\ChatBoat;
use App\Models\Messages\Messages;
use App\Models\Access\User\User;
use App\Models\TrackMessages\TrackMessages;
use App\Models\AdminMessages\AdminMessages;

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
                'user_id'               => $userInfo->id,
                'interested_user_id'    => $request->get('interested_user_id')
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
                    $userOne = User::find($userInfo->id);
                    $userTwo = User::find($request->get('interested_user_id'));
                    
                    $text = $userOne->name . ' is a Match and has been added to your Roster!';
                    
                    $notificationData = [
                        'title'                 => $text,
                        'user_id'               => $userOne->id,
                        'other_user_id'         => $userTwo->id,
                        'notification_type'     => 'MUTUAL_LIKE',
                        'badge_count'           => access()->getUnreadNotificationCount($userTwo->id)
                    ];

                    access()->addNotification($notificationData);
                    access()->sentPushNotification($userTwo, $notificationData);

                    $text2 = $userTwo->name . ' is a Match and has been added to your Roster!';
                    
                    $notificationData2 = [
                        'title'                 => $text2,
                        'user_id'               => $userTwo->id,
                        'other_user_id'         => $userOne->id,
                        'notification_type'     => 'MUTUAL_LIKE',
                        'badge_count'           => access()->getUnreadNotificationCount($userOne->id)
                    ];

                    access()->addNotification($notificationData2);
                    access()->sentPushNotification($userOne, $notificationData2);

                    $message = Messages::create([
                        'user_id'       => $userInfo->id,
                        'is_admin'      => 1,
                        'other_user_id' => $request->get('interested_user_id'),
                        'message'       => "Let's Talk "
                    ]);

                    AdminMessages::create([
                        'user_id'       => $userInfo->id,
                        'other_user_id' => $request->get('interested_user_id'),
                        'message_id'    => $message->id
                    ]);

                    $isExist = TrackMessages::where([
                        'user_id'               => $userInfo->id,
                        'other_user_id'         => $request->get('interested_user_id')
                    ])->orWhere([
                        'other_user_id'   => $request->get('interested_user_id'),
                        'user_id'         => $userInfo->id
                    ])->first();

                    if(isset($isExist))
                    {
                        $isExist->last_message_user_id      = $userInfo->id;
                        $isExist->is_admin                  = 1;
                        $isExist->last_message_created_at   = date('Y-m-d H:i:s');
                        $isExist->save();
                    }
                    else
                    {
                        TrackMessages::create([
                            'user_id'                   => $userInfo->id,
                            'other_user_id'             => $request->get('interested_user_id'),
                            'is_admin'                  => 1,
                            'last_message_user_id'      => $userInfo->id,
                            'last_message_created_at'   => date('Y-m-d H:i:s')
                        ]);
                    }
                    
                    $text3 = 'Kitbot has sent you a message.';
                    
                    $notificationData3 = [
                        'title'                 => $text3,
                        'user_id'               => $userTwo->id,
                        'other_user_id'         => $userOne->id,
                        'message_id'            => $message->id,
                        'notification_type'     => 'KITBOAT_MESSAGE',
                        'badge_count'           => access()->getUnreadNotificationCount($userOne->id)
                    ];

                    $notificationData4 = [
                        'title'                 => $text3,
                        'user_id'               => $userOne->id,
                        'other_user_id'         => $userTwo->id,
                        'message_id'            => $message->id,
                        'notification_type'     => 'KITBOAT_MESSAGE',
                        'badge_count'           => access()->getUnreadNotificationCount($userTwo->id)
                    ];

                    access()->addNotification($notificationData3);
                    access()->sentPushNotification($userOne, $notificationData3);

                    access()->addNotification($notificationData4);
                    access()->sentPushNotification($userTwo, $notificationData4);

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