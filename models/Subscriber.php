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
        'subscribed',
    ];

    /**
     * @var array Validation rules
     */
    public $rules = [
        'email' => 'required|email|unique:pbs_campaign_subscribers',
        'status' => 'in:active,bounced',
    ];

    /**
     * @var array Attributes to cast to native types
     */
    protected $casts = [
        'subscribed' => 'boolean',
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
    ];

    public function beforeCreate()
    {
        $this->hash = md5($this->email);
    }

    // this will run everytime..
    public function beforeSave()
    {
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
                $this->unsubscribed_at = null;
                $this->unsubscribed_by = null;
            }
        // }        

        // print_r($this->unsubscribed_at);
    }
}
