<?php namespace App\Models\SocialImages;

/**
 * Class SocialImages
 *
 * @author Anuj Jaha ( er.anujjaha@gmail.com)
 */

use App\Models\BaseModel;
use App\Models\SocialImages\Traits\Attribute\Attribute;
use App\Models\SocialImages\Traits\Relationship\Relationship;

class SocialImages extends BaseModel
{
    use Attribute, Relationship;
    /**
     * Database Table
     *
     */
    protected $table = "data_social_images";

    /**
     * Fillable Database Fields
     *
     */
    protected $fillable = [
        "id", "user_id", "image_url", "social_type", "created_at", "updated_at", 
    ];

    /**
     * Timestamp flag
     *
     */
    public $timestamps = true;

    /**
     * Guarded ID Column
     *
     */
    protected $guarded = ["id"];
}