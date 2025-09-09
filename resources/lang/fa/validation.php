<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Validation Language Lines
    |--------------------------------------------------------------------------
    |
    | The following language lines contain the default error messages used by
    | the validator class. Some of these rules have multiple versions such
    | as the size rules. Feel free to tweak each of these messages here.
    |
    */

    'accepted'             => ':attribute می بایست پذیرفته شود.',
    'active_url'           => ':attribute یک مسیر نامعتبر است.',
    'after'                => ':attribute می بایست یک تاریخ پس از :date باشد.',
    'after_or_equal'       => ':attribute می بایست در تاریخ :date یا پس از آن باشد.',
    'alpha'                => ':attribute فقط می بایست شامل حروف باشد.',
    'alpha_dash'           => ':attribute فقط می بایست شامل حروف،اعداد و خط تیره(-) باشد.',
    'alpha_num'            => ':attribute فقط می بایست شامل حروف و اعداد باشد.',
    'array'                => ':attribute فقط می بایست آرایه باشد.',
    'before'               => ':attribute می بایست یک تاریخ پیش از :date باشد.',
    'before_or_equal'      => ':attribute می بایست در تاریخ :date یا پیش از آن باشد.',
    'between'              => [
        'numeric' => ':attribute می بایست بین مقادیر :min و :max باشد .',
        'file'    => ':attribute می بایست بین مقادیر :min تا :max کیلوبایت باشد.',
        'string'  => ':attribute می بایست بین  :min تا :max کاراکتر باشد.',
        'array'   => ':attribute می بایست بین  :min تا :max آیتم باشد.',
    ],
    'boolean'              => ':attribute می بایست دارای مقادیر صحیح یا غلط باشد.',
    'confirmed'            => 'فرمت :attribute نا معتبر می باشد.',
    'date'                 => ':attribute یک تاریخ نامعتبر است.',
    'date_format'          => 'فرمت :attribute با فرمت :format مغایر می باشد.',
    'different'            => ':attribute and :other must be different.',
    'digits'               => ':attribute می بایست اعداد :digits باشند.',
    'digits_between'       => ':attribute می بایست بین اعداد :min و :max باشد.',
    'dimensions'           => ':attribute has invalid image dimensions.',
    'distinct'             => 'فیلد :attribute دارای مقادیر تکراری است.',
    'email'                => ':attribute می بایست یک ایمیل معتبر باشد.',
    'exists'               => ':attribute انتخابی، نامعتبراست.',
    'file'                 => ':attribute می بایست یک فایل باشد.',
    'filled'               => 'فیلد :attribute دارای مقدار نمی باشد.',
    'image'                => ':attribute می بایست یک تصویر باشد.',
    'in'                   => ':attribute انتخابی، نامعتبر است.',
    'in_array'             => 'فیلد :attribute در :other موجود نمی باشد.',
    'integer'              => 'فیلد :attribute می بایست اعداد صحیح باشد.',
    'ip'                   => ':attribute می بایست حاوی یک آدرس IP معتبر باشد.',
    'ipv4'                 => ':attribute می بایست حاوی یک آدرس IPv4 معتبر باشد.',
    'ipv6'                 => ':attribute می بایست حاوی یک آدرس IPv6 معتبر باشد.',
    'json'                 => ':attribute می بایست حاوی یک رشته JSON معتبر باشد',
    'max'                  => [
        'numeric' => ':attribute نباید از مقدار :max بزرگتر باشد.',
        'file'    => ':attribute نباید از مقدار :max کیلوبایت بزرگتر باشد .',
        'string'  => ':attribute نباید از  :max کاراکتر بیشتر باشد.',
        'array'   => ':attribute نباید از  :max آیتم بیشتر باشد.',
    ],
    'mimes'                => 'نوع فایل :attribute می بایست از نوع: :values باشد.',
    'mimetypes'            => 'نوع فایل :attribute می بایست از نوع: :values باشد.',
    'min'                  => [
        'numeric' => ':attribute نباید از مقدار :max کوچکتر باشد.',
        'file'    => ':attribute نباید از مقدار :max کیلوبایت کوچکتر باشد .',
        'string'  => ':attribute نباید از  :max کاراکتر کمتر باشد.',
        'array'   => ':attribute نباید از  :max آیتم کمتر باشد.',
    ],
    'not_in'               => ':attribute انتخابی، نامعتبر می باشد.',
    'numeric'              => ':attribute می بایست فقط اعداد باشد',
    'present'              => ':attribute می بایستی وارد شده باشد.',
    'regex'                => 'فرمت :attribute نامعتبر می باشد.',
    'required'             => ':attribute را وارد کنید.',
    'required_if'          => 'در صورتی که:other برابر :value مقدار :attribute می بایست وارد شود.',
    'required_unless'      => 'The :attribute field is required unless :other is in :values.',
    'required_with'        => 'The :attribute field is required when :values is present.',
    'required_with_all'    => 'The :attribute field is required when :values is present.',
    'required_without'     => 'The :attribute field is required when :values is not present.',
    'required_without_all' => 'The :attribute field is required when none of :values are present.',
    'same'                 => 'The :attribute and :other must match.',
    'size'                 => [
        'numeric' => 'The :attribute must be :size.',
        'file'    => 'The :attribute must be :size kilobytes.',
        'string'  => 'The :attribute must be :size characters.',
        'array'   => 'The :attribute must contain :size items.',
    ],
    'string'               => 'The :attribute must be a string.',
    'timezone'             => 'The :attribute must be a valid zone.',
    'unique'               => 'The :attribute has already been taken.',
    'uploaded'             => 'The :attribute failed to upload.',
    'url'                  => 'فرمت :attribute نا معتبر می باشد.',

    /*
    |--------------------------------------------------------------------------
    | Custom Validation Language Lines
    |--------------------------------------------------------------------------
    |
    | Here you may specify custom validation messages for attributes using the
    | convention "attribute.rule" to name the lines. This makes it quick to
    | specify a specific custom language line for a given attribute rule.
    |
    */

    'custom' => [
        'attribute-name' => [
            'rule-name' => 'custom-message',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Custom Validation Attributes
    |--------------------------------------------------------------------------
    |
    | The following language lines are used to swap attribute place-holders
    | with something more reader friendly such as E-Mail Address instead
    | of "email". This simply helps us make messages a little cleaner.
    |
    */

    'attributes' => [
        'title' => 'عنوان',
        'username' => 'نام کاربری',
        'password' => 'کلمه عبور',
        'amount' => 'مبلغ اعتبار',
        'food' => 'غذا',
        'user_group' => 'گروه کاربری',
        'meal' => 'وعده',
        'price' => 'قیمت',
        'id' => 'شناسه',
        'billId' => 'شماره قبض',
        'trackCode' => 'کد پیگیری تراکنش درگاه اینترنتی',
        'desc' => 'توضیحات',
        'message' => 'پیام',
        'receiver' => 'مخاطب',
        'foodTitle' => 'عنوان غذا',
        'foodPrice' => 'قیمت غذا',
        'optTitle' => 'عنوان مخلفات',
        'optPrice' => 'قیمت مخلفات',
        'groups' => 'مشتریان',
        'pic' => 'تصویر',
        'food_type' => 'نوع غذا',
    ],
];
