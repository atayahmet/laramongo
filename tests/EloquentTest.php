<?php

/**
 *  Mongo DB ORM test sınıfı
 *
 * Tüm testler users collection'ı altında çalıştırılacaktır
 * Testlerin sorunsuz çalışabilmesi için users adında bir collection oluşturulması gerekiyor
 *
 */
class EloquentTest extends Boot {
    private static $model;

    /**
     *
     * @method all
     *
     */
    public function testAll()
    {
        $user = self::$instance['user'];

        $total = count($user->all()->toArray());
        $countTotal = $user->count();

        $this->assertEquals($total, $countTotal);
    }

    /**
     *
     * @method find
     *
     */
    public function testFind()
    {
        $user = self::$instance['user'];

        // Yeni bir kullanıcı oluşturuluyor..
        $name = 'Ali';
        $lastname = 'Yılmaz';

        $user->firstname = $name;
        $user->lastname = $lastname;
        $user->save();

        // son kaydın insert id'si alınıyor.
        $lastId = $user->lastId();

        $lastUser = $user->find($lastId);

        $this->assertEquals($lastUser->firstname, $name);
    }

    /**
     *
     * @method findOrFail
     *
     */
    public function testFindOrFail()
    {
        $user = self::$instance['user'];

        // Yeni bir kullanıcı oluşturuluyor..
        $name = 'Ali';
        $lastname = 'Yılmaz';

        $user->firstname = $name;
        $user->lastname = $lastname;
        $user->save();

        // son kaydın insert id'si alınıyor.
        $lastId = $user->lastId();

        $usrResult = $user->findOrFail($lastId)->toArray();

        $this->assertGreaterThan(0, count($usrResult));

        // fail test
        $this->assertFalse($user->findOrFail('ddsfsfsdf', $testEnv = true));
    }
}