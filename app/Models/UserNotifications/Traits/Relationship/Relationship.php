<?php namespace App\Models\UserNotifications\Traits\Relationship;

use App\Models\Access\User\User;

trait Relationship
{
	/**
	 * Belongs To
	 *
	 * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
	 */
	public function user()
	{
	    return $this->belongsTo(User::class, 'user_id');
	}

	/**
	 * Belongs To
	 *
	 * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
	 */
	public function other_user()
	{
	    return $this->belongsTo(User::class, 'other_user_id');
	}	
}