<?php namespace Pbs\Campaign\Models;

use Backend\Models\ImportModel;

class SubscriberImport extends ImportModel
{
    public $table = 'pbs_campaign_subscribers';

    /**
     * @var array Rules
     */
    public $rules = [
        'email' => 'required|email'
    ];

    protected $fillable = [
        'name',
        'email',        
    ];

    public function importData($results, $sessionKey = null)
    {
        foreach ($results as $row => $data) {

            try {
                $subscriber = new Subscriber;
                $subscriber->fill($data);
                $subscriber->save();

                $this->logCreated();
            }
            catch (\Exception $ex) {
                $this->logError($row, $ex->getMessage());
            }

        }
    }
}