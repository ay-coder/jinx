<?php

namespace App\Repositories\Backend\Access\User;

use App\Models\Access\User\User;
use Illuminate\Support\Facades\DB;
use App\Exceptions\GeneralException;
use App\Repositories\BaseRepository;
use Illuminate\Database\Eloquent\Model;
use App\Events\Backend\Access\User\UserCreated;
use App\Events\Backend\Access\User\UserDeleted;
use App\Events\Backend\Access\User\UserUpdated;
use App\Events\Backend\Access\User\UserRestored;
use App\Events\Backend\Access\User\UserDeactivated;
use App\Events\Backend\Access\User\UserReactivated;
use App\Events\Backend\Access\User\UserPasswordChanged;
use App\Repositories\Backend\Access\Role\RoleRepository;
use App\Events\Backend\Access\User\UserPermanentlyDeleted;
use App\Notifications\Frontend\Auth\UserNeedsConfirmation;

/**
 * Class UserRepository.
 */
class UserRepository extends BaseRepository
{
    /**
     * Associated Repository Model.
     */
    const MODEL = User::class;

    /**
     * @var RoleRepository
     */
    protected $role;

    /**
     * @param RoleRepository $role
     */
    public function __construct()
    {
        $this->role = new RoleRepository;
    }

    /**
     * @param        $permissions
     * @param string $by
     *
     * @return mixed
     */
    public function getByPermission($permissions, $by = 'name')
    {
        if (! is_array($permissions)) {
            $permissions = [$permissions];
        }

        return $this->query()->whereHas('roles.permissions', function ($query) use ($permissions, $by) {
            $query->whereIn('permissions.'.$by, $permissions);
        })->get();
    }

    /**
     * @param        $roles
     * @param string $by
     *
     * @return mixed
     */
    public function getByRole($roles, $by = 'name')
    {
        if (! is_array($roles)) {
            $roles = [$roles];
        }

        return $this->query()->whereHas('roles', function ($query) use ($roles, $by) {
            $query->whereIn('roles.'.$by, $roles);
        })->get();
    }

    /**
     * @param int  $status
     * @param bool $trashed
     *
     * @return mixed
     */
    public function getForDataTable($status = 1, $trashed = false)
    {
        /**
         * Note: You must return deleted_at or the User getActionButtonsAttribute won't
         * be able to differentiate what buttons to show for each row.
         */
        $dataTableQuery = $this->query()
            ->with('roles')
            ->select([
                config('access.users_table').'.id',
                config('access.users_table').'.name',
                config('access.users_table').'.email',
                config('access.users_table').'.status',
                config('access.users_table').'.confirmed',
                config('access.users_table').'.created_at',
                config('access.users_table').'.updated_at',
                config('access.users_table').'.deleted_at',
            ]);

        if ($trashed == 'true') {
            return $dataTableQuery->onlyTrashed();
        }

        // active() is a scope on the UserScope trait
        return $dataTableQuery->active($status);
    }

    /**
     * @param Model $input
     */
    public function create($input)
    {
        $data = $input['data'];
        $roles = $input['roles'];

        $user = $this->createUserStub($data);

        DB::transaction(function () use ($user, $data, $roles) {
            if ($user->save()) {

                //User Created, Validate Roles
                if (! count($roles['assignees_roles'])) {
                    throw new GeneralException(trans('exceptions.backend.access.users.role_needed_create'));
                }

                //Attach new roles
                $user->attachRoles($roles['assignees_roles']);

                //Send confirmation email if requested
                if (isset($data['confirmation_email']) && $user->confirmed == 0) {
                    $user->notify(new UserNeedsConfirmation($user->confirmation_code));
                }

                event(new UserCreated($user));

                return true;
            }

            throw new GeneralException(trans('exceptions.backend.access.users.create_error'));
        });
    }

    /**
     * @param Model $user
     * @param array $input
     *
     * @return bool
     * @throws GeneralException
     */
    public function update(Model $user, array $input)
    {
        $data = $input['data'];
        $roles = $input['roles'];

        $this->checkUserByEmail($data, $user);

        DB::transaction(function () use ($user, $data, $roles) {
            if ($user->update($data)) {
                //For whatever reason this just wont work in the above call, so a second is needed for now
                $user->status = isset($data['status']) ? 1 : 0;
                $user->confirmed = isset($data['confirmed']) ? 1 : 0;
                $user->save();

                $this->checkUserRolesCount($roles);
                $this->flushRoles($roles, $user);

                event(new UserUpdated($user));

                return true;
            }

            throw new GeneralException(trans('exceptions.backend.access.users.update_error'));
        });
    }

    /**
     * @param Model $user
     * @param $input
     *
     * @throws GeneralException
     *
     * @return bool
     */
    public function updatePassword(Model $user, $input)
    {
        $user->password = bcrypt($input['password']);

        if ($user->save()) {
            event(new UserPasswordChanged($user));

            return true;
        }

        throw new GeneralException(trans('exceptions.backend.access.users.update_password_error'));
    }

    /**
     * @param Model $user
     *
     * @throws GeneralException
     *
     * @return bool
     */
    public function delete(Model $user)
    {
        if (access()->id() == $user->id) {
            throw new GeneralException(trans('exceptions.backend.access.users.cant_delete_self'));
        }

        if ($user->delete()) {
            event(new UserDeleted($user));

            return true;
        }

        throw new GeneralException(trans('exceptions.backend.access.users.delete_error'));
    }

    /**
     * @param Model $user
     *
     * @throws GeneralException
     */
    public function forceDelete(Model $user)
    {
        if (is_null($user->deleted_at)) {
            throw new GeneralException(trans('exceptions.backend.access.users.delete_first'));
        }

        DB::transaction(function () use ($user) {
            if ($user->forceDelete()) {
                event(new UserPermanentlyDeleted($user));

                return true;
            }

            throw new GeneralException(trans('exceptions.backend.access.users.delete_error'));
        });
    }

    /**
     * @param Model $user
     *
     * @throws GeneralException
     *
     * @return bool
     */
    public function restore(Model $user)
    {
        if (is_null($user->deleted_at)) {
            throw new GeneralException(trans('exceptions.backend.access.users.cant_restore'));
        }

        if ($user->restore()) {
            event(new UserRestored($user));

            return true;
        }

        throw new GeneralException(trans('exceptions.backend.access.users.restore_error'));
    }

    /**
     * @param Model $user
     * @param $status
     *
     * @throws GeneralException
     *
     * @return bool
     */
    public function mark(Model $user, $status)
    {
        if (access()->id() == $user->id && $status == 0) {
            throw new GeneralException(trans('exceptions.backend.access.users.cant_deactivate_self'));
        }

        $user->status = $status;

        switch ($status) {
            case 0:
                event(new UserDeactivated($user));
            break;

            case 1:
                event(new UserReactivated($user));
            break;
        }

        if ($user->save()) {
            return true;
        }

        throw new GeneralException(trans('exceptions.backend.access.users.mark_error'));
    }

    /**
     * @param  $input
     * @param  $user
     *
     * @throws GeneralException
     */
    protected function checkUserByEmail($input, $user)
    {
        //Figure out if email is not the same
        if ($user->email != $input['email']) {
            //Check to see if email exists
            if ($this->query()->where('email', '=', $input['email'])->first()) {
                throw new GeneralException(trans('exceptions.backend.access.users.email_error'));
            }
        }
    }

    /**
     * @param $roles
     * @param $user
     */
    protected function flushRoles($roles, $user)
    {
        //Flush roles out, then add array of new ones
        $user->detachRoles($user->roles);
        $user->attachRoles($roles['assignees_roles']);
    }

    /**
     * @param  $roles
     *
     * @throws GeneralException
     */
    protected function checkUserRolesCount($roles)
    {
        //User Updated, Update Roles
        //Validate that there's at least one role chosen
        if (count($roles['assignees_roles']) == 0) {
            throw new GeneralException(trans('exceptions.backend.access.users.role_needed'));
        }
    }

    /**
     * @param  $input
     *
     * @return mixed
     */
    public function createUserStub($input)
    {
        $userData = [
            'name'          => $input['name'],
            'email'         => isset($input['email']) ? $input['email'] : '',
            'username'      => $input['username'],
            'password'      => bcrypt($input['password']),
            'status'        => 1,
            'confirmed'     => 1,
            'device_token'  => isset($input['device_token']) ? $input['device_token']: '',
            'device_type'  => isset($input['device_type']) ? $input['device_type']: 1,
            'phone'         => isset($input['phone']) ? $input['phone']: '',
            'profile_pic'   => isset($input['profile_pic']) ? $input['profile_pic']: 'default.png',
            'gender'        => isset($input['gender']) ? $input['gender']: '',
            'bio'   => isset($input['bio']) ? $input['bio']: '',
        ];
        
        $user = User::create($userData);
        return $user;
    }

    /**
     * @param  $input
     *
     * @return mixed
     */
    public function updateUserStub($userId = null, $input = array())
    {
        if($userId && $input)
        {
            if(isset($input['password']) && strlen($input['password']) > 2)
            {
                $input['password'] = bcrypt($input['password']);
            }

            if(isset($input['id']))
            {
                unset($input['id']);
            }

            if(isset($input['email']))
            {
                unset($input['email']);
            }   
            
            return User::where('id', $userId)->update($input);
        }

        return false;
    }

    /**
     * @param  $input
     *
     * @return mixed
     */
    public function createSocialUserStub($input)
    {
        $userData = [
            'name'             => isset($input['name']) ? $input['name'] : '',
            'username'         => isset($input['social_token']) ? $input['social_token'] : '',
            'email'            => isset($input['email']) ? $input['email'] : '',
            'social_token'     => $input['social_token'],
            'password'         => isset($input['password']) ? bcrypt($input['password']) : bcrypt($input['name']),
            'status'        => 1,
            'confirmed'     => 1,
            'device_token'  => isset($input['device_token']) ? $input['device_token']: '',
            'phone'        => isset($input['phone']) ? $input['phone']: '',
            'device_type'   => isset($input['device_type']) ? $input['device_type']: 1,
            'profile_pic'   => isset($input['profile_pic']) ? $input['profile_pic'] : 'default.png',
            'address'       => isset($input['address']) ? $input['address']: '',
            'city'          => isset($input['city']) ? $input['city']: '',
            'insta_token'          => isset($input['insta_token']) ? $input['insta_token']: '',
            'spotify_token'          => isset($input['spotify_token']) ? $input['spotify_token']: '',
            'city'          => isset($input['city']) ? $input['city']: '',
            'latitude'          => isset($input['latitude']) ? $input['latitude']: '',
            'longitude'          => isset($input['longitude']) ? $input['longitude']: '',
            'state'         => isset($input['state']) ? $input['state']: '',
            'zip'           => isset($input['zip']) ? $input['zip']: '',
            'gender'        => isset($input['gender']) ? $input['gender']: '',
            'birthdate'     => isset($input['birthdate']) ? $input['birthdate']: '',
            'latitude'      => isset($input['latitude']) ? $input['latitude']: '',
            'longitude'     => isset($input['longitude']) ? $input['longitude']: '',
            'bio'           => isset($input['bio']) ? $input['bio']: '',
            'profession'    => isset($input['profession']) ? $input['profession']: '',
            'education'     => isset($input['education']) ? $input['education']: '',
        ];
        
        $user = User::create($userData);
        return $user;
    }
}
