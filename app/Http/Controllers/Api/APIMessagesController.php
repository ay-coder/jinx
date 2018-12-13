<?php
namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Http\Transformers\MessagesTransformer;
use App\Http\Controllers\Api\BaseApiController;
use App\Models\Access\User\User;
use App\Repositories\Messages\EloquentMessagesRepository;

class APIMessagesController extends BaseApiController
{
    /**
     * Messages Transformer
     *
     * @var Object
     */
    protected $messagesTransformer;

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
    protected $primaryKey = 'messagesId';

    /**
     * __construct
     *
     */
    public function __construct()
    {
        $this->repository                       = new EloquentMessagesRepository();
        $this->messagesTransformer = new MessagesTransformer();
    }

    /**
     * List of All Messages
     *
     * @param Request $request
     * @return json
     */
    public function index(Request $request)
    {
        $userInfo   = $this->getAuthenticatedUser();
        $userId     = $request->has('user_id') ? $request->get('user_id') : $userInfo->id;
        $messages   = $this->repository->getAllUserMessages($userId);

        if($messages && count($messages))
        {
            $itemsOutput = $this->messagesTransformer->messageTranform($messages);

            return $this->successResponse($itemsOutput);
        }
        
        return $this->setStatusCode(400)->failureResponse([
            'message' => 'Unable to find Messages!'
            ], 'No Messages Found !');
    }

    /**
     * List of All Messages
     *
     * @param Request $request
     * @return json
     */
    public function getChat(Request $request)
    {
        if($request->has('user_id') && $request->has('other_user_id'))
        {
            $messages = $this->repository->getAllChat($request->get('user_id'), $request->get('other_user_id'));   
            
            if($messages && count($messages))
            {
                $userInfo       = $this->getAuthenticatedUser();
                $readMessageIds = [];

                foreach($messages as $message)
                {
                    if($userInfo->id == $message->other_user_id)
                    {
                        $readMessageIds[] = $message->id;
                    }
                }


                // Set Read Message
                if(count($readMessageIds))
                {
                    $this->repository->model->whereIn('id', $readMessageIds)->update(['is_read' => 1]);
                }
                
                $itemsOutput = $this->messagesTransformer->messageTranform($messages);

                return $this->successResponse($itemsOutput);
            }
        }

        return $this->setStatusCode(400)->failureResponse([
            'message' => 'Unable to find Messages!'
            ], 'No Messages Found !');
        
    }

    /**
     * Create
     *
     * @param Request $request
     * @return string
     */
    public function create(Request $request)
    {
        if($request->has('other_user_id'))
        {
            $userInfo       = $this->getAuthenticatedUser();
            $model          = $this->repository->model->create([
                'user_id'       => $userInfo->id,
                'other_user_id' => $request->get('other_user_id'),
                'message'       => $request->has('message') ? $request->get('message') : ''
            ]);

            if($model)
            {
                $otherUser = User::find($request->get('other_user_id'));
                
                $text               = $userInfo->name . ' has sent you a message.';
                $notificationData   = [
                    'title'                 => $text,
                    'user_id'               => $userInfo->id,
                    'other_user_id'         => $otherUser->id,
                    'message_id'            => $model->id,
                    'notification_type'     => 'NEW_MESSAGE',
                    'badge_count'           => access()->getUnreadNotificationCount($otherUser->id)
                ];

                access()->addNotification($notificationData);
                access()->sentPushNotification($otherUser, $notificationData);


                $responseData = $this->messagesTransformer->singleMessageTranform($model);
                return $this->successResponse($responseData);
            }
        }

        return $this->setStatusCode(200)->failureResponse([
            'reason' => 'Invalid Other User Id'
            ], 'Invalid Other User Id!');
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
                $responseData = $this->messagesTransformer->transform($itemData);

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
                $responseData   = $this->messagesTransformer->transform($itemData);

                return $this->successResponse($responseData, 'Messages is Edited Successfully');
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
        if($request->has('message_id'))
        {
            $model      = $this->repository->model->where('id', $request->get('message_id'))->first();
            $userInfo   = $this->getAuthenticatedUser();

            if(isset($model) && ($model->user_id == $userInfo->id || $model->other_user_id == $userInfo->id))
            {
                $model->delete();  

                return $this->successResponse([
                    'success' => 'Messages Deleted Successfully'
                ], 'Messages Deleted Successfully'); 
            }
            
        }

        return $this->setStatusCode(404)->failureResponse([
            'reason' => 'Unable to Delete Message or No Message Found!'
        ], 'Unable to Delete Message or No Message Found!');
    }
}