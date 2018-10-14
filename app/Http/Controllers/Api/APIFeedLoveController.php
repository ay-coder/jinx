<?php
namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Http\Transformers\FeedLoveTransformer;
use App\Http\Controllers\Api\BaseApiController;
use App\Repositories\FeedLove\EloquentFeedLoveRepository;
use App\Models\Feeds\Feeds;

class APIFeedLoveController extends BaseApiController
{
    /**
     * FeedLove Transformer
     *
     * @var Object
     */
    protected $feedloveTransformer;

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
    protected $primaryKey = 'feedloveId';

    /**
     * __construct
     *
     */
    public function __construct()
    {
        $this->repository                       = new EloquentFeedLoveRepository();
        $this->feedloveTransformer = new FeedLoveTransformer();
    }

    /**
     * Love Post
     * 
     * @param Request $request
     * @return json
     */
    public function lovePost(Request $request)
    {
        if($request->has('feed_id'))
        {
            $isLike     = $request->has('love') ? $request->get('love') : 1;
            $userInfo   = $this->getAuthenticatedUser();
            $model      = $this->repository->model->where([
                'user_id' => $userInfo->id,
                'feed_id' => $request->get('feed_id')
            ])->first();

            if($isLike == 0)
            {
                if(isset($model) && isset($model->id))
                {
                    if($model->delete())
                    {
                        $message = [
                            'message' => 'Love removed successfully'
                        ];
                        return $this->successResponse($message, 'Love removed successfully');
                    }
                }
                else
                {
                    return $this->setStatusCode(400)->failureResponse([
                        'message' => 'No feed Found !'
                        ], 'No Feed Found !'); 
                }
            }

            if($isLike == 1)
            {
                if(isset($model) && isset($model->id))
                {
                    return $this->setStatusCode(400)->failureResponse([
                        'message' => 'Already Loved !'
                        ], 'Already Liked!'); 
                }
                else
                {
                    $status = $this->repository->model->create([
                        'user_id' => $userInfo->id,
                        'feed_id' => $request->get('feed_id'),
                    ]);

                    if($status)
                    {
                        $feedInfo  = Feeds::with(['user', 'feed_tag_users', 'feed_tag_users.user'])->where('id', $request->get('feed_id'))->first();
                        $text       = $userInfo->name . ' loved  your post.';

                        if(isset($feedInfo->user) && $userInfo->id != $feedInfo->user->id)
                        {
                            $payload = [
                                'mtitle'            => '',
                                'mdesc'             => $text,
                                'feed_id'           => $request->get('feed_id'),
                                'to_user_id'        => $feedInfo->user->id,
                                'feed_type'         => $feedInfo->feed_type,
                                'from_user_id'      => $userInfo->id,
                                'badgeCount'        => access()->getUnreadNotificationCount($feedInfo->user->id),
                                'mtype'             => 'FEED_LOVE'
                            ];

                            $storeNotification = [
                                'user_id'           => $feedInfo->user->id,
                                'from_user_id'      => $userInfo->id,
                                'description'       => $text,
                                'icon'              => 'FEED_LOVE.png',
                                'feed_id'           => $request->get('feed_id'),
                                'notification_type' => 'FEED_LOVE'
                            ];

                            access()->addNotification($storeNotification);
                            access()->sentPushNotification($feedInfo->user, $payload);
                        }

                        if(isset($feedInfo->feed_tag_users) && count($feedInfo->feed_tag_users))
                        {
                            foreach($feedInfo->feed_tag_users as $tagUser)
                            {
                                if($userInfo->id == $tagUser->user->id)
                                {
                                    continue;
                                }

                                if($feedInfo->user->id == $tagUser->user->id)
                                {
                                    continue;
                                }

                                if(isset($tagUser->group_id))
                                {
                                    $text = $userInfo->name . ' loved a post your group is tagged in.';
                                }
                                else
                                {
                                    $text = $userInfo->name . ' loved a post you are tagged in.';
                                }

                                $payload = [
                                    'mtitle'            => '',
                                    'mdesc'             => $text,
                                    'feed_id'           => $request->get('feed_id'),
                                    'feed_type'         => $feedInfo->feed_type,
                                    'to_user_id'        => $tagUser->user->id,
                                    'from_user_id'      => $userInfo->id,
                                    'mtype'             => 'FEED_LOVE_TAG_USERS'
                                ];

                                $storeNotification = [
                                    'user_id'           => $tagUser->user->id,
                                    'from_user_id'      => $userInfo->id,
                                    'description'       => $text,
                                    'icon'              => 'FEED_LOVE.png',
                                    'feed_id'           => $request->get('feed_id'),
                                    'notification_type' => 'FEED_LOVE_TAG_USERS'
                                ];

                                access()->addNotification($storeNotification);
                                access()->sentPushNotification($tagUser->user, $payload);
                            }
                        }

                        $message = [
                            'message' => 'Feed Love successfully'
                        ];
                        return $this->successResponse($message, 'Feed Loved successfully');
                    }
                }
            }
        }

        return $this->setStatusCode(400)->failureResponse([
            'reason' => 'No Feed Found or Invalid Input'
            ], 'Something went wrong !');
    }

    /**
     * List of All FeedLove
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
            $itemsOutput = $this->feedloveTransformer->transformCollection($items);

            return $this->successResponse($itemsOutput);
        }

        return $this->setStatusCode(400)->failureResponse([
            'message' => 'Unable to find FeedLove!'
            ], 'No FeedLove Found !');
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
            $responseData = $this->feedloveTransformer->transform($model);

            return $this->successResponse($responseData, 'FeedLove is Created Successfully');
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
                $responseData = $this->feedloveTransformer->transform($itemData);

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
                $responseData   = $this->feedloveTransformer->transform($itemData);

                return $this->successResponse($responseData, 'FeedLove is Edited Successfully');
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
                    'success' => 'FeedLove Deleted'
                ], 'FeedLove is Deleted Successfully');
            }
        }

        return $this->setStatusCode(404)->failureResponse([
            'reason' => 'Invalid Inputs'
        ], 'Something went wrong !');
    }
}