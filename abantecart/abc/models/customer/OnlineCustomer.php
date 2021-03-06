<?php

namespace abc\models\customer;

use abc\models\BaseModel;

/**
 * Class OnlineCustomer
 *
 * @property int $customer_id
 * @property string $ip
 * @property string $url
 * @property string $referer
 * @property \Carbon\Carbon $date_added
 *
 * @package abc\models
 */
class OnlineCustomer extends BaseModel
{
    protected $primaryKey = 'ip';
    public $incrementing = false;
    public $timestamps = false;

    protected $casts = [
        'customer_id' => 'int',
    ];

    protected $dates = [
        'date_added',
    ];

    protected $fillable = [
        'customer_id',
        'url',
        'referer',
        'date_added',
    ];
}
