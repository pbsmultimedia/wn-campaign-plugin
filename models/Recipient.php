<?php namespace Pbs\Campaign\Models;

use Model;

class Recipient extends Model
{
    use \Winter\Storm\Database\Traits\Validation;

    /**
     * @var string The database table used by the model.
     */
    public $table = 'pbs_campaign_recipients';

    /**
     * @var array Guarded fields
     */
    protected $guarded = [];

    /**
     * @var array Fillable fields
     */
    protected $fillable = [];

    /**
     * @var array Validation rules for attributes
     */
    public $rules = [];

    /**
     * @var array Attributes to be cast to native types
     */
    protected $casts = [];

    /**
     * @var array Attributes to be cast to JSON
     */
    protected $jsonable = [];

    /**
     * @var array Attributes to be appended to the API representation of the model (ex. toArray())
     */
    protected $appends = [];

    /**
     * @var array Attributes to be removed from the API representation of the model (ex. toArray())
     */
    protected $hidden = [];

    /**
     * @var array Attributes to be cast to Argon (Carbon) instances
     */
    protected $dates = [
        'created_at',
        'updated_at'
    ];

    public $belongsTo = [
        'subscriber' => [Subscriber::class, 'key' => 'subscriber_id', 'otherKey' => 'id']
    ];

    public $hasMany = [
        'clicks' => Click::class
    ];

    public function getNameAttribute()
    {
        return $this->subscriber?->name;
    }

    public function getFirstNameAttribute()
    {
        return $this->subscriber?->first_name;
    }

    public function getEmailAttribute()
    {
        return $this->subscriber?->email ?? 'deleted';
    }
    
    public function getHashAttribute()
    {
        return $this->subscriber?->hash;
    }
}
