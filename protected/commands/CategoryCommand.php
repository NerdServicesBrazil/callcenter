<?php
class CategoryCommand extends ConsoleCommand
{
    public $config;

    public function run($args)
    {
        $day = date('Y-m-d');

        //$day = "2015-12-03";
        sleep(15);
        $modelCdr = Cdr::model()->findAll(array(
            'select'    => 'count(*) as countCall, id, id_phonenumber'
            'condition' => 'starttime > :key AND id_category IS NULL',
            'params'    => array('key' => $day),
            'group'     => 'id_phonenumber',
        ));

        foreach ($modelCdr as $key => $cdr) {

            $modelPhoneNumber = PhoneNumber::model()->findByPk((int) $cdr->id_phonenumber);

            //se o numero ainda for categoria ativo ou inativo, deixar para o proximo minuto
            if (!isset($modelPhoneNumber->id_category) || $modelPhoneNumber->id_category < 2) {
                continue;
            } else if ($cdr['count'] == 1) {
                $cdr->id_category = $modelPhoneNumber->id_category;
                $cdr->save();

            } else {
                PhoneNumber::model()->updateAll(array('id_category' => 3), 'id_phonenumber = :key', array(':key' => $cdr->id_phonenumber));

                $modelCdr2 = PhoneNumber::model()->find(array(
                    'condition' => 'id_phonenumber = :key',
                    'params'    => array(':key' => $cdr->id_phonenumber)
                    'order'     => 'id DESC',
                ));
                $modelCdr2->id_category = $modelPhoneNumber->id_category;
                $modelCdr2->save();
            }
        }

    }
}