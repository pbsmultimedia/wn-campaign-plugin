<?php namespace Pbs\Campaign\Models;

use Model;

class Subscriber extends Model
{
    use \Winter\Storm\Database\Traits\Validation;
    /**
     * @var string The database table used by the model.
     */
    public $table = 'pbs_campaign_subscribers';

    /**
     * @var array Fillable fields
     */
    protected $fillable = [
        'name',
        'email',
        'status',
        'is_subscribed',
        'verified_at',
        'consent_given_at',
        'consent_version',
        'source',
        'ip',
        'locale',
        'unsubscribe_campaign_id',
        'unsubscribe_reason',
    ];

    /**
     * @var array Validation rules
     */
    public $rules = [
        'email' => 'required|email|unique:pbs_campaign_subscribers',
        'status' => 'in:active,bounced,pending',
    ];

    /**
     * @var array Attributes to be cast to Argon (Carbon) instances
     */
    public $dates = [
        'verified_at',
        'consent_given_at',
        'unsubscribed_at',
        'created_at',
        'updated_at',
    ];

    /**
     * @var array Attributes to cast to native types
     */
    protected $casts = [
        'is_subscribed' => 'boolean',
    ];

    /**
     * Relations
     */
    public $belongsToMany = [
        'lists' => [
            MailingList::class,
            'table' => 'pbs_campaign_list_subscriber',
            'key' => 'subscriber_id',
            'otherKey' => 'list_id',
        ],
    ];

    public $hasMany = [
        'clicks' => [Click::class, 'key' => 'subscriber_id'],
        'visualizations' => [Visualization::class, 'key' => 'subscriber_id'],
    ];

    public function beforeCreate()
    {
        $this->hash = md5($this->email);
    }

    // this will run everytime..
    public function beforeSave()
    {
        // Convert empty strings to null for date fields
        foreach (['verified_at', 'consent_given_at', 'unsubscribed_at'] as $field) {
            if ($this->{$field} === '') {
                $this->{$field} = null;
            }
        }

        // bounce webhook - not implemented yet
        if (!post('Subscriber')) {
            return;
        }

        $subscriber = post('Subscriber');
        
        // unsubscribe by system
        // if (isset($subscriber['unsubscribed_by'])) {
            if (!$subscriber['unsubscribed_by'] && !$subscriber['is_subscribed']) {            
                $this->is_subscribed = false;
                $this->unsubscribed_at = now();
                // TODO: use enums / consts
                $this->unsubscribed_by = 'system';
            // subscribe by system
            } elseif ($subscriber['unsubscribed_by'] == 'system' && $subscriber['is_subscribed']) {                
                $this->is_subscribed = true;        
                $this->unsubscribed_at = null;
                $this->unsubscribed_by = null;
            } else {
                // If we are updating from the backend, and unsubscribed_by is NOT set in the post data,
                // we should probably keep existing values unless they are explicitly being cleared.
                // However, the original code was resetting them to null.
                // I'll keep the original logic but ensure empty strings are handled by the loop above.
                $this->unsubscribed_at = null;
                $this->unsubscribed_by = null;
            }
        // }        

        // print_r($this->unsubscribed_at);
    }

    /**
     * Get the first name of the subscriber.
     * Think the name used on the newsletter body already..? But there guess it's the full name?
     * TODO: test this on the newsletter body
     */
    public function getFirstNameAttribute()
    {
        if (!$this->name) {
            return null;
        }

        return explode(' ', trim($this->name))[0];
    }
}
