<?php

namespace App\Services\Access;

use Illuminate\Contracts\Auth\Authenticatable;
use App\Models\ReadPost\ReadPost;
use App\Models\Connections\Connections;
use App\Models\UserNotifications\UserNotifications;
/*use App\Models\FeedNotifications\FeedNotifications;*/
use App\Library\Push\PushNotification;
use App\Models\Settings\Settings;
use App\Models\BlockUsers\BlockUsers;
use App\Models\Messages\Messages;
use App\Models\TempBlock\TempBlock;
use App\Models\UserInterests\UserInterests;
use Carbon\Carbon;
use App\Models\TrackMessages\TrackMessages;
use App\Models\Access\User\User;

/**
 * Class Access.
 */
class Access
{
    /**
     * Get the currently authenticated user or null.
     */
    public function user()
    {
        return auth()->user();
    }

    /**
     * Return if the current session user is a guest or not.
     *
     * @return mixed
     */
    public function guest()
    {
        return auth()->guest();
    }

    /**
     * @return mixed
     */
    public function logout()
    {
        return auth()->logout();
    }

    /**
     * Get the currently authenticated user's id.
     *
     * @return mixed
     */
    public function id()
    {
        return auth()->id();
    }

    /**
     * @param Authenticatable $user
     * @param bool            $remember
     */
    public function login(Authenticatable $user, $remember = false)
    {
        return auth()->login($user, $remember);
    }

    /**
     * @param $id
     *
     * @return mixed
     */
    public function loginUsingId($id)
    {
        return auth()->loginUsingId($id);
    }

    /**
     * Checks if the current user has a Role by its name or id.
     *
     * @param string $role Role name.
     *
     * @return bool
     */
    public function hasRole($role)
    {
        if ($user = $this->user()) {
            return $user->hasRole($role);
        }

        return false;
    }

    /**
     * Checks if the user has either one or more, or all of an array of roles.
     *
     * @param  $roles
     * @param bool $needsAll
     *
     * @return bool
     */
    public function hasRoles($roles, $needsAll = false)
    {
        if ($user = $this->user()) {
            return $user->hasRoles($roles, $needsAll);
        }

        return false;
    }

    /**
     * Check if the current user has a permission by its name or id.
     *
     * @param string $permission Permission name or id.
     *
     * @return bool
     */
    public function allow($permission)
    {
        if ($user = $this->user()) {
            return $user->allow($permission);
        }

        return false;
    }

    /**
     * Check an array of permissions and whether or not all are required to continue.
     *
     * @param  $permissions
     * @param  $needsAll
     *
     * @return bool
     */
    public function allowMultiple($permissions, $needsAll = false)
    {
        if ($user = $this->user()) {
            return $user->allowMultiple($permissions, $needsAll);
        }

        return false;
    }

    /**
     * @param  $permission
     *
     * @return bool
     */
    public function hasPermission($permission)
    {
        return $this->allow($permission);
    }

    /**
     * @param  $permissions
     * @param  $needsAll
     *
     * @return bool
     */
    public function hasPermissions($permissions, $needsAll = false)
    {
        return $this->allowMultiple($permissions, $needsAll);
    }

    /**
     * Get ReadPost Ids
     * 
     * @param int $userId
     * @return array
     */
    public function getReadPostIds($userId = null)
    {
        if($userId)
        {
            return ReadPost::where('user_id', $userId)->pluck('post_id');
        }
        
        return [];
    }    

    /**
     * Get My ConnectionIds
     * 
     * @param int $userId
     * @return array
     */
    public function getMyConnectionIds($userId = null)
    {
        if($userId)   
        {
            $connectionModel        = new Connections;
            $myConnectionList       = $connectionModel->where([
                'user_id'       => $userId,
                'is_accepted'   => 1
            ])->pluck('other_user_id')->toArray();

            $otherConnectionList    = $connectionModel->where([
                'other_user_id' => $userId,
                'is_accepted'   => 1
            ])->pluck('requested_user_id')->toArray();

            $allConnections         = array_merge($myConnectionList, $otherConnectionList);
            $allConnections         = array_unique($allConnections);

            return $allConnections;
        }

        return [];
    }

    /**
     * Get My Request Ids
     * 
     * @param int $userId
     * @return array
     */
    public function getMyRequestIds($userId = null)
    {
        if($userId)   
        {
            $connectionModel        = new Connections;
            $myConnectionList       = $connectionModel->where([
                'user_id'       => $userId,
                'is_accepted'   => 0
            ])->pluck('other_user_id')->toArray();

            $otherConnectionList    = $connectionModel->where([
                'other_user_id' => $userId,
                'is_accepted'   => 0
            ])->pluck('requested_user_id')->toArray();

            $allConnections         = array_merge($myConnectionList, $otherConnectionList);
            $allConnections         = array_unique($allConnections);

            return $allConnections;
        }

        return [];
    }

    /**
     * Get My Request Ids
     * 
     * @param int $userId
     * @return array
     */
    public function getOnlyMyRequestIds($userId = null)
    {
        if($userId)   
        {
            $connectionModel        = new Connections;
            $myConnectionList       = $connectionModel->where([
                'user_id'           => $userId,
                'requested_user_id' => $userId,
                'is_accepted'       => 0
            ])->pluck('other_user_id')->toArray();

            $allConnections = array_unique($myConnectionList);

            return $allConnections;
        }

        return [];
    }

    /**
     * Get My Request Ids
     * 
     * @param int $userId
     * @return array
     */
    public function getOnlyReceiveRequestIds($userId = null)
    {
        if($userId)   
        {
            $connectionModel        = new Connections;
            $myConnectionList       = $connectionModel->where([
                'other_user_id'     => $userId,
                'is_accepted'       => 0
            ])
            ->pluck('user_id')->toArray();

            $allConnections = array_unique($myConnectionList);

            return $allConnections;
        }

        return [];
    }

    /**
     * Get Load More Flag
     * 
     * @param object $model
     * @return bool
     */
    public function getLoadMoreFlag($model = null)
    {

    }


    /**
     * Add Notification
     *
     * @param array $data
     */
    public function addNotification($data = array())
    {
        if(isset($data) && count($data))
        {
            return UserNotifications::create($data);
        }

        return false;
    }


    /**
     * Sent Push Notification
     * 
     * @param object $user
     * @param array $payload
     * @return bool
     */
    public function sentPushNotification($user = null, $payload = null)
    {
        if($user && $payload)
        {
            if(isset($user->device_token) && strlen($user->device_token) > 4)
            {
                PushNotification::iOS($payload, $user->device_token);
            }
        }

        return true;
    }

    /**
     * Get Unread Notification Count
     * 
     * @param int $userId
     * @return int
     */
    public function getUnreadNotificationCount($userId = null)
    {
        if($userId)
        {
            return UserNotifications::where([
                'is_read'   => 0,
                'user_id'   => $userId
            ])->count();
        }

        return 1;

    }

    /**
     * Get User Settings
     * 
     * @param int $userId
     * @return object
     */
    public function getUserSettings($userId = null)
    {
        if($userId)
        {
            return Settings::where('user_id', $userId)->first();
        }

        return false;
    }

    /**
     * Get User Settings
     * 
     * @param int $userId
     * @return object
     */
    public function getMyBlockedUserIds($userId = null)
    {
        if($userId)
        {
            return BlockUsers::where('user_id', $userId)->pluck('block_user_id')->toArray();
        }

        return false;
    }

    /**
     * Get User Settings
     * 
     * @param int $userId
     * @return object
     */
    public function getMyTempBlockedUserIds($userId = null)
    {
        if($userId)
        {
            return TempBlock::where('user_id', $userId)->pluck('block_user_id')->toArray();
        }

        return false;
    }

    /**
     * Get Random Question
     * 
     * @return array
     */
    public function getRandomQuestion()
    {
        $questiions = [
            [
                'question_id' => 1,
                'question'    => 'What is Your Pet Name ?'
            ],
            [
                'question_id' => 2,
                'question'    => 'What is Your Birth Place ?'
            ],
            [
                'question_id' => 3,
                'question'    => 'What is Your Favorite Color ?'
            ],
        ];

        $randomNumber = rand(0, 2);
        return $questiions[$randomNumber];
    }

    /**
     * Get Unread Message Count
     * 
     * @param int $userId
     * @return int
     */
    public function getUnreadMessageCount($userId = null, $otherUserId = null)
    {
        if($userId && $otherUserId)
        {
            return Messages::where([
                'is_read'       => 0,
                'other_user_id' => $userId,
                'user_id'       => $otherUserId
            ])->count();
        }

        return 0;
    }

    /**
     * Get Unread Message Count
     * 
     * @param int $userId
     * @return int
     */
    public function getUnreadUserMessageCount($userId = null)
    {
        if($userId)
        {
            return Messages::where([
                'is_read'       => 0,
                'user_id'       => $userId,
            ])->count();
        }

        return 0;
    }

    /**
     * Get My Roaster UserIds
     * 
     * @param int $userId
     * @return array
     */
    public function getMyRoasterUserIds($userId = null)
    {
        if($userId)
        {
            $myInterestIds =  UserInterests::where('user_id', $userId)->pluck('interested_user_id')->toArray();

            $otherInterestIds = UserInterests::where([
                'interested_user_id' => $userId,
                'is_accepted'        => 1
            ])->pluck('user_id')->toArray();

            return array_unique(array_merge($myInterestIds, $otherInterestIds));
        }

        return [];
    }

    /**
     * Check Kit Boat Messages
     * 
     * @return true
     */
    public function checkKitBoatMessages()
    {
        $trackMessages = TrackMessages::where('last_message_created_at', '<', Carbon::now()->subDays(1)->toDateTimeString())->get();

        // Try with Every Messages
        //$trackMessages = TrackMessages::get();

        if(isset($trackMessages) && count($trackMessages))
        {
            foreach($trackMessages as $trackMessage)
            {
                if($trackMessage->is_admin == 1)
                {
                    $userOne = User::find($trackMessage->user_id);
                    $userTwo = User::find($trackMessage->other_user_id);
                    
                    $text = 'Its been a long time '. $userOne->name .' has heard from you.';

                    $messageOne = Messages::create([
                        'user_id'       => $userOne->id,
                        'is_admin'      => 1,
                        'other_user_id' => $userTwo->id,
                        'message'       => $text
                    ]);

                    
                    $notificationData = [
                        'title'                 => $text,
                        'user_id'               => $userOne->id,
                        'other_user_id'         => $userTwo->id,
                        'message_id'            => $messageOne->id,
                        'notification_type'     => 'KITBOAT_REMINDER_MESSAGE',
                        'badge_count'           => access()->getUnreadNotificationCount($userTwo->id)
                    ];

                    $this->addNotification($notificationData);
                    $this->sentPushNotification($userTwo, $notificationData);

                    $text2 = 'Its been a long time '. $userTwo->name .' has heard from you.';
                    
                    $messageTwo = Messages::create([
                        'user_id'       => $userTwo->id,
                        'is_admin'      => 1,
                        'other_user_id' => $userOne->id,
                        'message'       => $text2
                    ]);

                    $notificationData2 = [
                        'title'                 => $text2,
                        'message_id'            => $messageTwo->id,
                        'user_id'               => $userTwo->id,
                        'other_user_id'         => $userOne->id,
                        'notification_type'     => 'KITBOAT_REMINDER_MESSAGE',
                        'badge_count'           => $this->getUnreadNotificationCount($userOne->id)
                    ];

                    $this->addNotification($notificationData2);
                    $this->sentPushNotification($userOne, $notificationData2);

                    continue;
                }

                if($trackMessage->last_message_user_id == $trackMessage->user_id)
                {
                    $userOne = User::find($trackMessage->user_id);
                    $userTwo = User::find($trackMessage->other_user_id);
                    
                    $text = $userOne->name .' is wating to hear from you.';

                    $message = Messages::create([
                        'user_id'       => $userOne->id,
                        'is_admin'      => 1,
                        'other_user_id' => $userTwo->id,
                        'message'       => $text
                    ]);
                    
                    $notificationData = [
                        'title'                 => $text,
                        'user_id'               => $userOne->id,
                        'other_user_id'         => $userTwo->id,
                        'message_id'            => $message->id,
                        'notification_type'     => 'KITBOAT_WAITING_MESSAGE',
                        'badge_count'           => $this->getUnreadNotificationCount($userTwo->id)
                    ];

                    $this->addNotification($notificationData);
                    $this->sentPushNotification($userTwo, $notificationData);

                    continue;
                }

                if($trackMessage->last_message_user_id == $trackMessage->other_user_id)
                {
                    $userOne = User::find($trackMessage->other_user_id);
                    $userTwo = User::find($trackMessage->user_id);
                    
                    $text = $userOne->name .' is wating to hear from you.';

                    $message = Messages::create([
                        'user_id'       => $userOne->id,
                        'is_admin'      => 1,
                        'other_user_id' => $userTwo->id,
                        'message'       => $text
                    ]);
                    
                    $notificationData = [
                        'title'                 => $text,
                        'user_id'               => $userOne->id,
                        'other_user_id'         => $userTwo->id,
                        'message_id'            => $message->id,
                        'notification_type'     => 'KITBOAT_WAITING_MESSAGE',
                        'badge_count'           => $this->getUnreadNotificationCount($userTwo->id)
                    ];

                    $this->addNotification($notificationData);
                    $this->sentPushNotification($userTwo, $notificationData);

                    continue;
                }
            }
        }
    }

    /**
     * Check Temp Block Users
     *
     * @return bool
     */
    public function checkTempBlockUsers()
    {
        return TempBlock::where('created_at', '<', Carbon::now()->subDays(1)->toDateTimeString())->delete();
    }
}


