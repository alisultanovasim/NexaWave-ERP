<?php

return [
    'type' => [
        '1' => [
            'name' => 'Əmək müqaviləsinin bağlanması',
            'description' => 'İşəgötürənlə işçi arasında fərdi qaydada bağlanan əmək münasibətlərinin əsas şərtlərini, tərəflərin hüquq və vəzifələrini əks etdirən yazılı müqavilədir.',
            'route' => 'closed'
        ],
        '2' => [
            'name' => 'Əmək müqaviləsinə xitam verilməsi',
            'description' => 'Əmək Məcəlləsində nəzərdə tutulan əsaslara və qaydalara müvafiq olaraq əmək müqaviləsinin ləğv edilməsi işəgötürənin əsaslandırılmış əmri (sərəncamı, qərarı) ilə rəsmiləşdirilir.',
            'route' => 'ended'
        ],
        '3' => [
            'name' => 'Başka işə keçirilmə',
            'description' => 'İşçiyə əmək müqaviləsində nəzərdə tutulmayan başqa peşə, ixtisas və vəzifə üzrə əmək funksiyasının yerinə yetirilməsini həvalə etmək başqa işə keçirilmə sayılır və buna yalnız işçinin razılığı ilə, həmçinin əmək müqaviləsinə əlavə və dəyişikliklər edilməklə və ya yeni əmək müqaviləsi bağlanılmaqla yol verilir.;',
            'route' => 'exchange'
        ],
        '4' => [
            'name' => 'Əmək Məzuniyyəti',
            'description' => 'Əmək məzuniyyəti işverənlə əmək münasibətlərində olan işçinin əməyinin və istehsalın xarakterinə görə normal istirahəti, əmək qabiliyyətinin bərpası, sağlamlığının mühafizəsi və möhkəmləndirilməsi üçün işdən ayrılmaqla öz mülahizəsi ilə istifadə etdiyi istirahət vaxtıdır.',
            'route' => 'vacation-work'
        ],
        '5' => [
            'name' => 'Təhsil Məzuniyyəti',
            'description' => 'Laboratoriya işlərinin yerinə yetirilməsi, yoxlamaların və imtahanların verilməsi üçün, diplom layihəsinin (işinin) hazırlandığı və müdafiə edildiyi dövr üçün təhsil məzuniyyəti verilir',
            'route' => 'vacation-educate'
        ],
        '6' => [
            'name' => ' Ödənişsiz Məzuniyyət',
            'description' => 'İşçi ilə işəgötürən arasındakı əmək münasibətlərində nəzərdə tutulan məzuniyyət növlərindən biridir. Bu ya könüllü (işçinin istəyi ilə) və ya məcburi (işəgötürənin istəyi ilə) verilə bilər',
            'route' => 'vacation-free'
        ],
        '7' => [
            'name' => 'SSocial Məzuniyyət',
            'description' => ' İşçilərə analıq, uşaq baxımı, iş təhsili, ailə və məişət ehtiyaclarını ödəmək və digər sosial məqsədlər üçün əlverişli şərait yaratmaq məqsədi ilə verilir',
            'route' => 'vacation-social'
        ],
        '8' => [
            'name' => 'Ezamiyyət',
            'description' => ' Dövlət orqanının, müəssisə, idarə və təşkilatın rəhbərinin sərəncamı (əmri) ilə işçinin daimi iş yerindən müəyyən olunmuş müddətə başqa yerə xidməti tapşırığı yerinə yetirmək üçün getməsi xidməti ezamiyyət sayılır.',
            'route' => ''
        ],
    ]
];
