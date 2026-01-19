<?php namespace Pbs\Campaign\Models;

use Model;

class MailingList extends Model
{
    use \October\Rain\Database\Traits\Validation;

    /**
     * @var string The database table used by the model.
     */
    public $table = 'pbs_campaign_lists';

    /**
     * @var array Guarded fields
     */
    protected $guarded = ['*'];

    /**
     * @var array Fillable fields
     */
    protected $fillable = [
        'name',
    ];

    /**
     * @var array Validation rules
     */
    public $rules = [
        'name' => 'required|unique:pbs_campaign_lists',
    ];

    /**
     * Relations
     */
    public $belongsToMany = [
        'subscribers' => [
            Subscriber::class,
            'table' => 'pbs_campaign_list_subscriber',
            'key' => 'list_id',
            'otherKey' => 'subscriber_id',
            // 'scope' => 'subscribed'
            // even better, no scope is needed, just set the condition
            'conditions' => 'is_subscribed = 1'
        ],
    ];

    /**
     * Get the list option label including subscriber count
     */
    public function getListNameWithCount()
    {
        return sprintf('%s (%d subscribers)', $this->name, $this->subscribers()->count());
    }

    /**
     * Get options array for relation widgets
     */
    public static function getDropdownOptions($key = null, $data = [])
    {
        $lists = self::all();
        $options = [];

        foreach ($lists as $list) {
            $options[$list->id] = $list->getListNameWithCount();
        }

        return $options;
    }

    public function getTitleAttribute($id)
    {
        // return $id;
        // return $this->name . ' (' . $this->subscribers()->find($this->id)->count() . ' email)';
        // return $this->subscribers();
        return $this->name;
    }

    public function scopeHasSubscribers($query)
    {
        return $query->whereHas('subscribers');
    }

    // Add this accessor
    public function getSubscribersCountAttribute()
    {
        return $this->subscribers()->count();
    }    
}
