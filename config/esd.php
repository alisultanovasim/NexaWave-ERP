<?php
//
return [
    "document" => [
        "status" => [
            "wait" => 0,
            "active" => 1,
            "draft" => 2,  //qaralama
            "done" => 3,
            'archive' => 4
        ],
        "assignment" => [ //inferior
            "done" => 2,
            "wait" => 1,
            "not_seen" => 0
        ],
        "ableToChangeStatus" => [3, 2],
        "type" => [
            "file" => 1,
            "editor" => 2
        ],
        "extensions" => ["xls", "xlsx", "docx", "doc", "pdf"],
        'adjustments' => [
            'type' => [
                'raw' => 1,
                'action' => 2
            ],
            /**
             * dont touch initial value!!!!
             */
            'initial_rules' => [
                'coulmns9' => [
                    ['name' => 'Bölmə', 'position' => '1', 'is_active' => '1', 'type' => '1', 'field' => 'section',],
                    ['name' => 'Qeydiyyat Nömrəsi', 'position' => '2', 'is_active' => '1', 'type' => '1', 'field' => 'register_number',],
                    ['name' => 'Mövzu', 'position' => '3', 'is_active' => '1', 'type' => '1', 'field' => 'theme',],
                    ['name' => 'Qeydiyyat tarixi', 'position' => '4', 'is_active' => '1', 'type' => '1', 'field' => 'register_time',],
                    ['name' => 'Göndərilmə Növü', 'position' => '5', 'is_active' => '0', 'type' => '1', 'field' => 'send_type',],
                    ['name' => 'Müraciət Forması', 'position' => '6', 'is_active' => '0', 'type' => '1', 'field' => 'send_form',],
                    ['name' => 'Vərəq sayı', 'position' => '7', 'is_active' => '0', 'type' => '1', 'field' => 'page_count',],
                    ['name' => 'Nüsxə sayı', 'position' => '8', 'is_active' => '0', 'type' => '1', 'field' => 'copy_count',],
                    ['name' => 'Sənədin tarixi', 'position' => '9', 'is_active' => '0', 'type' => '1', 'field' => 'document_time',],
                    ['name' => 'Sənəd Nömrəsi', 'position' => '10', 'is_active' => '0', 'type' => '1', 'field' => 'document_no',],
                    ['name' => 'Statusu dəyiş', 'position' => '1', 'is_active' => '1', 'type' => '2', 'field' => '/assets/images/svg/status change.svg',],
                    ['name' => 'Müddətin artırılması', 'position' => '2', 'is_active' => '0', 'type' => '2', 'field' => '/assets/images/svg/passage-of-time.svg',],
                    ['name' => 'Sənədlər', 'position' => '3', 'is_active' => '1', 'type' => '3', 'field' => '/assets/images/svg/file.svg',],
                ],

                'coulmns12' => [
                    ['name' => 'Bölmə', 'position' => '0', 'is_active' => '1', 'type' => '1', 'field' => 'section',],
                    ['name' => 'İcraçı', 'position' => '1', 'is_active' => '1', 'type' => '1', 'field' => 'stuck',],
                    ['name' => 'Qeydiyyat Nömrəsi', 'position' => '2', 'is_active' => '1', 'type' => '1', 'field' => 'register_number',],
                    ['name' => 'Status', 'position' => '3', 'is_active' => '1', 'type' => '1', 'field' => 'status',],
                    ['name' => 'Mövzu', 'position' => '4', 'is_active' => '1', 'type' => '1', 'field' => 'theme',],
                    ['name' => 'Qeydiyyat tarixi', 'position' => '5', 'is_active' => '1', 'type' => '1', 'field' => 'register_time',],
                    ['name' => 'İcra tarixi', 'position' => '6', 'is_active' => '1', 'type' => '1', 'field' => 'expire_time',],
                    ['name' => 'Göndərilmə Növü', 'position' => '7', 'is_active' => '0', 'type' => '1', 'field' => 'send_type',],
                    ['name' => 'Müraciət Forması', 'position' => '8', 'is_active' => '0', 'type' => '1', 'field' => 'send_form',],
                    ['name' => 'Vərəq sayı', 'position' => '9', 'is_active' => '0', 'type' => '1', 'field' => 'page_count',],
                    ['name' => 'Nüsxə sayı', 'position' => '10', 'is_active' => '0', 'type' => '1', 'field' => 'copy_count',],
                    ['name' => 'Sənədin tarixi', 'position' => '11', 'is_active' => '0', 'type' => '1', 'field' => 'document_time',],
                    ['name' => 'Sənəd Nömrəsi', 'position' => '12', 'is_active' => '0', 'type' => '1', 'field' => 'document_no',],
                    ['name' => 'Sənədlər', 'position' => '1', 'is_active' => '1', 'type' => '2', 'field' => '/assets/images/svg/file.svg',],
                    ['name' => 'Redaktə et', 'position' => '2', 'is_active' => '1', 'type' => '2', 'field' => '/assets/images/svg/edit.svg',],
                    ['name' => 'Statusu dəyiş', 'position' => '3', 'is_active' => '1', 'type' => '2', 'field' => '/assets/images/svg/status change.svg',],
                    ['name' => 'Dərkənar', 'position' => '4', 'is_active' => '1', 'type' => '2', 'field' => '/assets/images/svg/instructions.svg',],
                    ['name' => 'Müddətin artırılması', 'position' => '5', 'is_active' => '0', 'type' => '2', 'field' => '/assets/images/svg/passage-of-time.svg',],
                    ['name' => 'Cavab Yaz', 'position' => '6', 'is_active' => '0', 'type' => '2', 'field' => '/assets/images/svg/email.svg',],
                    ['name' => 'Adiyyatı üzrə göndər', 'position' => '7', 'is_active' => '0', 'type' => '2', 'field' => '/assets/images/svg/mail-send.svg',],],


                'coulmns1' => [
                    ['name' => 'İcraçı', 'position' => '0', 'is_active' => '1', 'type' => '1', 'field' => 'stuck',],
//                    ['name' => 'Bölmə', 'position' => '1', 'is_active' => '0', 'type' => '1', 'field' => 'section',],
                    ['name' => 'Qeydiyyat Nömrəsi', 'position' => '2', 'is_active' => '1', 'type' => '1', 'field' => 'register_number',],
                    ['name' => 'Status', 'position' => '3', 'is_active' => '1', 'type' => '1', 'field' => 'status',],
                    ['name' => 'Kimdən Ünvanlanıb', 'position' => '4', 'is_active' => '1', 'type' => '1', 'field' => 'sender_company_id_id',],
                    ['name' => 'Kimə Ünvanlanıb', 'position' => '5', 'is_active' => '1', 'type' => '1', 'field' => 'company_user',],
                    ['name' => 'Mövzu', 'position' => '6', 'is_active' => '1', 'type' => '1', 'field' => 'theme',],
                    ['name' => 'Qeydiyyat tarixi', 'position' => '7', 'is_active' => '1', 'type' => '1', 'field' => 'register_time',],
                    ['name' => 'İcra tarixi', 'position' => '8', 'is_active' => '1', 'type' => '1', 'field' => 'expire_time',],
                    ['name' => 'Göndərilmə Növü', 'position' => '9', 'is_active' => '0', 'type' => '1', 'field' => 'send_type',],
                    ['name' => 'Müraciət Forması', 'position' => '10', 'is_active' => '0', 'type' => '1', 'field' => 'send_form',],
                    ['name' => 'Vərəq sayı', 'position' => '11', 'is_active' => '0', 'type' => '1', 'field' => 'page_count',],
                    ['name' => 'Nüsxə sayı', 'position' => '12', 'is_active' => '0', 'type' => '1', 'field' => 'copy_count',],
                    ['name' => 'Sənədin tarixi', 'position' => '13', 'is_active' => '0', 'type' => '1', 'field' => 'document_time',],
                    ['name' => 'Sənəd Nömrəsi', 'position' => '14', 'is_active' => '0', 'type' => '1', 'field' => 'document_no',],
                    ['name' => 'Daxil Edən', 'position' => '15', 'is_active' => '0', 'type' => '1', 'field' => 'from',],
                    ['name' => 'Sənədlər', 'position' => '1', 'is_active' => '1', 'type' => '2', 'field' => '/assets/images/svg/file.svg',],
                    ['name' => 'Redaktə et', 'position' => '2', 'is_active' => '1', 'type' => '2', 'field' => '/assets/images/svg/edit.svg',],
                    ['name' => 'Statusu dəyiş', 'position' => '3', 'is_active' => '1', 'type' => '2', 'field' => '/assets/images/svg/status change.svg',],
                    ['name' => 'Dərkənar', 'position' => '4', 'is_active' => '1', 'type' => '2', 'field' => '/assets/images/svg/instructions.svg',],
                    ['name' => 'Müddətin artırılması', 'position' => '5', 'is_active' => '0', 'type' => '2', 'field' => '/assets/images/svg/passage-of-time.svg',],
                    ['name' => 'Cavab Yaz', 'position' => '6', 'is_active' => '0', 'type' => '2', 'field' => '/assets/images/svg/email.svg',],
                    ['name' => 'Adiyyatı üzrə göndər', 'position' => '7', 'is_active' => '0', 'type' => '2', 'field' => '/assets/images/svg/mail-send.svg',]
                    ,],


                'coulmns2' => [
                    ['name' => 'İcraçı', 'position' => '0', 'is_active' => '1', 'type' => '1', 'field' => 'stuck',],
//                    ['name' => 'Bölmə', 'position' => '1', 'is_active' => '0', 'type' => '1', 'field' => 'section',],
                    ['name' => 'Qeydiyyat Nömrəsi', 'position' => '2', 'is_active' => '1', 'type' => '1', 'field' => 'register_number',],
                    ['name' => 'Status', 'position' => '3', 'is_active' => '1', 'type' => '1', 'field' => 'status',],
                    ['name' => 'Kimdən Ünvanlanıb', 'position' => '4', 'is_active' => '1', 'type' => '1', 'field' => 'name',],
                    ['name' => 'Region', 'position' => '5', 'is_active' => '1', 'type' => '1', 'field' => 'region_id',],
                    ['name' => 'Kimə Ünvanlanıb', 'position' => '6', 'is_active' => '1', 'type' => '1', 'field' => 'company_user',],
                    ['name' => 'Mövzu', 'position' => '7', 'is_active' => '1', 'type' => '1', 'field' => 'theme',],
                    ['name' => 'Qeydiyyat tarixi', 'position' => '8', 'is_active' => '1', 'type' => '1', 'field' => 'register_time',],
                    ['name' => 'İcra tarixi', 'position' => '9', 'is_active' => '1', 'type' => '1', 'field' => 'expire_time',],
                    ['name' => 'Göndərilmə Növü', 'position' => '10', 'is_active' => '0', 'type' => '1', 'field' => 'send_type',],
                    ['name' => 'Müraciət Forması', 'position' => '11', 'is_active' => '0', 'type' => '1', 'field' => 'send_form',],
                    ['name' => 'Vərəq sayı', 'position' => '12', 'is_active' => '0', 'type' => '1', 'field' => 'page_count',],
                    ['name' => 'Nüsxə sayı', 'position' => '13', 'is_active' => '0', 'type' => '1', 'field' => 'copy_count',],
                    ['name' => 'Vətəndaşın Ünvanı', 'position' => '14', 'is_active' => '0', 'type' => '1', 'field' => 'region_id',],
                    ['name' => 'Sənədin tarixi', 'position' => '15', 'is_active' => '0', 'type' => '1', 'field' => 'document_time',],
                    ['name' => 'Sənəd Nömrəsi', 'position' => '16', 'is_active' => '0', 'type' => '1', 'field' => 'document_no',],
                    ['name' => 'Daxil Edən', 'position' => '17', 'is_active' => '0', 'type' => '1', 'field' => 'from',],
                    ['name' => 'Sənədlər', 'position' => '1', 'is_active' => '1', 'type' => '2', 'field' => '/assets/images/svg/file.svg',],
                    ['name' => 'Redaktə et', 'position' => '2', 'is_active' => '1', 'type' => '2', 'field' => '/assets/images/svg/edit.svg',],
                    ['name' => 'Statusu dəyiş', 'position' => '3', 'is_active' => '1', 'type' => '2', 'field' => '/assets/images/svg/status change.svg',],
                    ['name' => 'Dərkənar', 'position' => '4', 'is_active' => '1', 'type' => '2', 'field' => '/assets/images/svg/instructions.svg',],
                    ['name' => 'Müddətin artırılması', 'position' => '5', 'is_active' => '0', 'type' => '2', 'field' => '/assets/images/svg/passage-of-time.svg',],
                    ['name' => 'Cavab Yaz', 'position' => '6', 'is_active' => '0', 'type' => '2', 'field' => '/assets/images/svg/email.svg',],
                    ['name' => 'Adiyyatı üzrə göndər', 'position' => '7', 'is_active' => '0', 'type' => '2', 'field' => '/assets/images/svg/mail-send.svg',],],


                'coulmns34' => [
                    ['name' => 'Bölmə', 'position' => '0', 'is_active' => '1', 'type' => '1', 'field' => 'section',],
                    ['name' => 'İcraçı', 'position' => '1', 'is_active' => '1', 'type' => '1', 'field' => 'stuck',],
                    ['name' => 'Qeydiyyat Nömrəsi', 'position' => '2', 'is_active' => '1', 'type' => '1', 'field' => 'register_number',],
                    ['name' => 'Status', 'position' => '3', 'is_active' => '1', 'type' => '1', 'field' => 'status',],
                    ['name' => 'Mövzu', 'position' => '4', 'is_active' => '1', 'type' => '1', 'field' => 'theme',],
                    ['name' => 'Qeydiyyat tarixi', 'position' => '5', 'is_active' => '1', 'type' => '1', 'field' => 'register_time',],
                    ['name' => 'İcra tarixi', 'position' => '6', 'is_active' => '1', 'type' => '1', 'field' => 'expire_time',],
                    ['name' => 'Göndərilmə Növü', 'position' => '7', 'is_active' => '0', 'type' => '1', 'field' => 'send_type',],
                    ['name' => 'Müraciət Forması', 'position' => '8', 'is_active' => '0', 'type' => '1', 'field' => 'send_form',],
                    ['name' => 'Vərəq sayı', 'position' => '9', 'is_active' => '0', 'type' => '1', 'field' => 'page_count',],
                    ['name' => 'Nüsxə sayı', 'position' => '10', 'is_active' => '0', 'type' => '1', 'field' => 'copy_count',],
                    ['name' => 'Sənədin tarixi', 'position' => '11', 'is_active' => '0', 'type' => '1', 'field' => 'document_time',],
                    ['name' => 'Sənəd Nömrəsi', 'position' => '12', 'is_active' => '0', 'type' => '1', 'field' => 'document_no',],
                    ['name' => 'Sənədlər', 'position' => '1', 'is_active' => '1', 'type' => '2', 'field' => '/assets/images/svg/file.svg',],
                    ['name' => 'Redaktə et', 'position' => '2', 'is_active' => '1', 'type' => '2', 'field' => '/assets/images/svg/edit.svg',],
                    ['name' => 'Statusu dəyiş', 'position' => '3', 'is_active' => '1', 'type' => '2', 'field' => '/assets/images/svg/status change.svg',],
                    ['name' => 'Müddətin artırılması', 'position' => '5', 'is_active' => '0', 'type' => '2', 'field' => '/assets/images/svg/passage-of-time.svg',],
                    ['name' => 'Cavab Yaz', 'position' => '6', 'is_active' => '0', 'type' => '2', 'field' => '/assets/images/svg/email.svg',],
                    ['name' => 'Adiyyatı üzrə göndər', 'position' => '7', 'is_active' => '0', 'type' => '2', 'field' => '/assets/images/svg/mail-send.svg',],],


                'coulmns3' => [
                    ['name' => 'İcraçı', 'position' => '0', 'is_active' => '1', 'type' => '1', 'field' => 'stuck',],
//                    ['name' => 'Bölmə', 'position' => '1', 'is_active' => '0', 'type' => '1', 'field' => 'section',],
                    ['name' => 'Qeydiyyat Nömrəsi', 'position' => '2', 'is_active' => '1', 'type' => '1', 'field' => 'register_number',],
                    ['name' => 'Status', 'position' => '3', 'is_active' => '1', 'type' => '1', 'field' => 'status',],
                    ['name' => 'Kimdən Ünvanlanıb', 'position' => '4', 'is_active' => '1', 'type' => '1', 'field' => 'company_user',],
                    ['name' => 'Kimə Ünvanlanıb', 'position' => '5', 'is_active' => '1', 'type' => '1', 'field' => 'sender_company_id',],
                    ['name' => 'Mövzu', 'position' => '6', 'is_active' => '1', 'type' => '1', 'field' => 'theme',],
                    ['name' => 'Qeydiyyat tarixi', 'position' => '7', 'is_active' => '1', 'type' => '1', 'field' => 'register_time',],
                    ['name' => 'İcra tarixi', 'position' => '8', 'is_active' => '1', 'type' => '1', 'field' => 'expire_time',],
                    ['name' => 'Göndərilmə Növü', 'position' => '9', 'is_active' => '0', 'type' => '1', 'field' => 'send_type',],
                    ['name' => 'Müraciət Forması', 'position' => '10', 'is_active' => '0', 'type' => '1', 'field' => 'send_form',],
                    ['name' => 'Vərəq sayı', 'position' => '11', 'is_active' => '0', 'type' => '1', 'field' => 'page_count',],
                    ['name' => 'Nüsxə sayı', 'position' => '12', 'is_active' => '0', 'type' => '1', 'field' => 'copy_count',],
                    ['name' => 'Sənədin tarixi', 'position' => '13', 'is_active' => '0', 'type' => '1', 'field' => 'document_time',],
                    ['name' => 'Sənəd Nömrəsi', 'position' => '14', 'is_active' => '0', 'type' => '1', 'field' => 'document_no',],
                    ['name' => 'Daxil Edən', 'position' => '15', 'is_active' => '0', 'type' => '1', 'field' => 'from',],
                    ['name' => 'Sənədlər', 'position' => '1', 'is_active' => '1', 'type' => '2', 'field' => '/assets/images/svg/file.svg',],
                    ['name' => 'Redaktə et', 'position' => '2', 'is_active' => '1', 'type' => '2', 'field' => '/assets/images/svg/edit.svg',],
                    ['name' => 'Statusu dəyiş', 'position' => '3', 'is_active' => '1', 'type' => '2', 'field' => '/assets/images/svg/status change.svg',],
                    ['name' => 'Müddətin artırılması', 'position' => '5', 'is_active' => '0', 'type' => '2', 'field' => '/assets/images/svg/passage-of-time.svg',],
                    ['name' => 'Cavab Yaz', 'position' => '6', 'is_active' => '0', 'type' => '2', 'field' => '/assets/images/svg/email.svg',],
                    ['name' => 'Adiyyatı üzrə göndər', 'position' => '7', 'is_active' => '0', 'type' => '2', 'field' => '/assets/images/svg/mail-send.svg',],
                ],


                'coulmns4' => [
                    ['name' => 'İcraçı', 'position' => '0', 'is_active' => '1', 'type' => '1', 'field' => 'stuck',],
//                    ['name' => 'Bölmə', 'position' => '1', 'is_active' => '0', 'type' => '1', 'field' => 'section',],
                    ['name' => 'Qeydiyyat Nömrəsi', 'position' => '2', 'is_active' => '1', 'type' => '1', 'field' => 'register_number',],
                    ['name' => 'Status', 'position' => '3', 'is_active' => '1', 'type' => '1', 'field' => 'status',],
                    ['name' => 'Kimdən Ünvanlanıb', 'position' => '4', 'is_active' => '1', 'type' => '1', 'field' => 'company_user',],
                    ['name' => 'Region', 'position' => '5', 'is_active' => '1', 'type' => '1', 'field' => 'region_id',],
                    ['name' => 'Kimə Ünvanlanıb', 'position' => '6', 'is_active' => '1', 'type' => '1', 'field' => 'name',],
                    ['name' => 'Mövzu', 'position' => '7', 'is_active' => '1', 'type' => '1', 'field' => 'theme',],
                    ['name' => 'Qeydiyyat tarixi', 'position' => '8', 'is_active' => '1', 'type' => '1', 'field' => 'register_time',],
                    ['name' => 'İcra tarixi', 'position' => '9', 'is_active' => '1', 'type' => '1', 'field' => 'expire_time',],
                    ['name' => 'Göndərilmə Növü', 'position' => '10', 'is_active' => '0', 'type' => '1', 'field' => 'send_type',],
                    ['name' => 'Müraciət Forması', 'position' => '11', 'is_active' => '0', 'type' => '1', 'field' => 'send_form',],
                    ['name' => 'Vərəq sayı', 'position' => '12', 'is_active' => '0', 'type' => '1', 'field' => 'page_count',],
                    ['name' => 'Nüsxə sayı', 'position' => '13', 'is_active' => '0', 'type' => '1', 'field' => 'copy_count',],
                    ['name' => 'Vətəndaşın Ünvanı', 'position' => '14', 'is_active' => '0', 'type' => '1', 'field' => 'region_id',],
                    ['name' => 'Sənədin tarixi', 'position' => '15', 'is_active' => '0', 'type' => '1', 'field' => 'document_time',],
                    ['name' => 'Sənəd Nömrəsi', 'position' => '16', 'is_active' => '0', 'type' => '1', 'field' => 'document_no',],
                    ['name' => 'Daxil Edən', 'position' => '17', 'is_active' => '0', 'type' => '1', 'field' => 'from',],
                    ['name' => 'Sənədlər', 'position' => '1', 'is_active' => '1', 'type' => '2', 'field' => '/assets/images/svg/file.svg',],
                    ['name' => 'Redaktə et', 'position' => '2', 'is_active' => '1', 'type' => '2', 'field' => '/assets/images/svg/edit.svg',],
                    ['name' => 'Statusu dəyiş', 'position' => '3', 'is_active' => '1', 'type' => '2', 'field' => '/assets/images/svg/status change.svg',],
                    ['name' => 'Müddətin artırılması', 'position' => '5', 'is_active' => '0', 'type' => '2', 'field' => '/assets/images/svg/passage-of-time.svg',],
                    ['name' => 'Cavab Yaz', 'position' => '6', 'is_active' => '0', 'type' => '2', 'field' => '/assets/images/svg/email.svg',],
                    ['name' => 'Adiyyatı üzrə göndər', 'position' => '7', 'is_active' => '0', 'type' => '2', 'field' => '/assets/images/svg/mail-send.svg',],],


                'coulmns5' => [
                    ['name' => 'İcraçı', 'position' => '0', 'is_active' => '1', 'type' => '1', 'field' => 'stuck',],
//                    ['name' => 'Bölmə', 'position' => '1', 'is_active' => '0', 'type' => '1', 'field' => 'section',],
                    ['name' => 'Qeydiyyat Nömrəsi', 'position' => '2', 'is_active' => '1', 'type' => '1', 'field' => 'register_number',],
                    ['name' => 'Status', 'position' => '3', 'is_active' => '1', 'type' => '1', 'field' => 'status',],
                    ['name' => 'Kimdən Ünvanlanıb', 'position' => '4', 'is_active' => '1', 'type' => '1', 'field' => 'from_in_our_company',],
                    ['name' => 'Kimə Ünvanlanıb', 'position' => '5', 'is_active' => '1', 'type' => '1', 'field' => 'to_in_our_company',],
                    ['name' => 'Mövzu', 'position' => '6', 'is_active' => '1', 'type' => '1', 'field' => 'theme',],
                    ['name' => 'Qeydiyyat tarixi', 'position' => '7', 'is_active' => '1', 'type' => '1', 'field' => 'register_time',],
                    ['name' => 'İcra tarixi', 'position' => '8', 'is_active' => '1', 'type' => '1', 'field' => 'expire_time',],
                    ['name' => 'Göndərilmə Növü', 'position' => '9', 'is_active' => '0', 'type' => '1', 'field' => 'send_type',],
                    ['name' => 'Müraciət Forması', 'position' => '10', 'is_active' => '0', 'type' => '1', 'field' => 'send_form',],
                    ['name' => 'Vərəq sayı', 'position' => '11', 'is_active' => '0', 'type' => '1', 'field' => 'page_count',],
                    ['name' => 'Nüsxə sayı', 'position' => '12', 'is_active' => '0', 'type' => '1', 'field' => 'copy_count',],
                    ['name' => 'Sənədin tarixi', 'position' => '13', 'is_active' => '0', 'type' => '1', 'field' => 'document_time',],
                    ['name' => 'Sənəd Nömrəsi', 'position' => '14', 'is_active' => '0', 'type' => '1', 'field' => 'document_no',],
                    ['name' => 'Daxil Edən', 'position' => '15', 'is_active' => '0', 'type' => '1', 'field' => 'from',],
                    ['name' => 'Sənədlər', 'position' => '1', 'is_active' => '1', 'type' => '2', 'field' => '/assets/images/svg/file.svg',],
                    ['name' => 'Redaktə et', 'position' => '2', 'is_active' => '1', 'type' => '2', 'field' => '/assets/images/svg/edit.svg',],
                    ['name' => 'Statusu dəyiş', 'position' => '3', 'is_active' => '1', 'type' => '2', 'field' => '/assets/images/svg/status change.svg',],
                    ['name' => 'Dərkənar', 'position' => '4', 'is_active' => '1', 'type' => '2', 'field' => '/assets/images/svg/instructions.svg',],
                    ['name' => 'Müddətin artırılması', 'position' => '5', 'is_active' => '0', 'type' => '2', 'field' => '/assets/images/svg/passage-of-time.svg',],
                    ['name' => 'Cavab Yaz', 'position' => '6', 'is_active' => '0', 'type' => '2', 'field' => '/assets/images/svg/email.svg',],
                    ['name' => 'Adiyyatı üzrə göndər', 'position' => '7', 'is_active' => '0', 'type' => '2', 'field' => '/assets/images/svg/mail-send.svg'
                        ,],]

            ]
        ],
        'data' => [
            'send_types' => false,
            'send_forms' => false,
            'sections' => false,
            'assignment_templates' => true,
            'regions' => false,
            'sender_companies' => true,
        ],

    ],
    'tables' => [
        'in_company_docs' => ['from_in_our_company', 'to_in_our_company'],
        'citizen_docs' => ['name', 'region_id', 'address'],
        'structure_docs' => ['sender_company_id', 'sender_company_role_id', 'sender_company_user_id']
    ],
    'table_relations' => [
        'in_company_docs' => ['fromInOurCompany' , 'toInOurCompany'],
        'citizen_docs' => ['region' , 'companyUser'],
        'structure_docs' => ['senderCompany', 'senderCompanyUser', 'senderCompanyRole' , 'companyUser']
    ]
];
