<?php namespace Pbs\Campaign\Models;

use Model;

class Mail extends Model
{
    use \October\Rain\Database\Traits\Validation;

    /**
     * @var string The database table used by the model.
     */
    public $table = 'indikator_newsletter_mails';

    /**
     * @var array Guarded fields
     */
    protected $guarded = ['*'];

    /**
     * @var array Fillable fields
     */
    protected $fillable = [
        'title',
        'content',
        'status',
        'category_id',
        'published_at',
        'sent_at',
    ];

    /**
     * @var array Validation rules
     */
    public $rules = [
        'title' => 'required|min:3',
        'content' => 'required',
        'status' => 'required|in:draft,sending,sent',
    ];
}
