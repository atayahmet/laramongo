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
     * @method findOrFail
     */
    public function testFindOrFail()
    {
        self::$model = self::$instance['user'];
        // Yeni bir kullanıcı oluşturuluyor..
        $name = 'Ali';
        $lastname = 'Yılmaz';

        self::$model->firstname = $name;
        self::$model->lastname = $lastname;
        self::$model->age = 18;
        self::$model->save();

        // son kaydın insert id'si alınıyor.
        $lastId = self::$model->lastId();
        $usrResult = self::$model->findOrFail($lastId, $testEnv = true);

        $this->assertGreaterThan(0, count($usrResult));

        // fail test
        $this->assertFalse(self::$model->findOrFail('blablabla', $testEnv = true));
    }

    /**
     * @method find
     */
    public function testFind()
    {
        self::$model = self::$instance['user'];
        // Yeni bir kullanıcı oluşturuluyor..
        $name = 'Ali';
        $lastname = 'Yılmaz';

        self::$model->firstname = $name;
        self::$model->lastname = $lastname;
        self::$model->age = 18;
        self::$model->save();

        // son kaydın insert id'si alınıyor.
        $lastId = self::$model->lastId();

        $lastUser = self::$model->find($lastId);
        $this->assertEquals($lastUser->firstname, $name);

        $newFirstname = 'Ali Can';
        $lastUser->firstname = $newFirstname;
        $lastUser->save();

        $lastUser = self::$model->find($lastId);
        $this->assertEquals($lastUser->firstname, $newFirstname);
    }


    /**
     * @method all
     */
    public function testAll()
    {
        self::$model = self::$instance['user'];

        $total = count(self::$model->all()->toArray());
        $countTotal = self::$model->count();

        $this->assertEquals($total, $countTotal);
    }


    /**
     * @method firstOrFail
     */
    public function testFirstOrFail()
    {
        self::$model = self::$instance['user'];
        self::$model->withTrashed()->where('lastname','Yılmaz')->restore();
        $name1 = self::$model->where('firstname','Ali')->firstOrFail($testEnv = true)->toArray()[0]['firstname'];

        $name2 = self::$model->where('firstname','Ali')->firstOrFail($testEnv = true)->toArray()[0]['firstname'];

        $this->assertEquals($name1, $name2);

        // fail test
        $fail = self::$model->where('firstname','bla blabla')->firstOrFail($testEnv = true);

        $this->assertFalse($fail);
    }

    /**
     * @method take
     */
    public function testTake()
    {
        $take = 3;

        $names = self::$model->where('firstname','Ali')->take($take)->get()->toArray();

        $this->assertEquals(count($names), $take);
    }

    /**
     * @method count
     */
    public function testCount()
    {
        $totalAll = count(self::$model->all()->toArray());
        $totalCount = self::$model->count();

        $this->assertEquals($totalAll, $totalCount);
    }

    /**
     * @method whereRaw
     */
    public function testWhereRaw()
    {
        $ali = self::$model->whereRaw(array( '$and' => array( array('firstname' => 'Ali'), array('lastname' => 'Yılmaz'))))->get();

        $this->assertTrue(is_array($ali->toArray()));
    }

    /**
     * @method on
     */
    public function testOn()
    {
        $result = self::$model->on('connect2')->first();

        $this->assertTrue(is_array($result->toArray()));
    }

    /**
     * @fillable
     */
    public function testFillable()
    {
        // fillable field firstname
        $firstname = 'blabla-' . rand(0,1000);

        // not able
        $lastname = 'blabla-' . rand(0,1001);

        self::$model->firstname = $firstname;
        self::$model->lastname = $lastname;
        self::$model->age = 18;
        self::$model->save();

        $fname = self::$model->where('firstname',$firstname)->count();
        $this->assertGreaterThan(0,$fname);

        $lname = self::$model->where('lastname',$lastname)->count();
        $this->assertEquals(0,$lname);
    }

    /**
     * @guarded
     */
    public function testGuarded()
    {
        // unguarded
        $firstname = 'blablaa-' . rand(0,1000);
        $lastname = 'blablaa-' . rand(0,1001);

        // guarded
        $address = 'Istanbul-' . rand(0,1001);

        self::$model->firstname = $firstname;
        self::$model->lastname = $lastname;
        self::$model->age = 18;
        self::$model->address = $address;
        self::$model->save();

        $fname = self::$model->where('firstname',$firstname)->count();
        $this->assertGreaterThan(0,$fname);

        $lname = self::$model->where('address',$address)->count();
        $this->assertEquals(0,$lname);
    }

    /**
     * @method save
     */
    public function testSave()
    {
        self::$model->firstname = 'Erkan' ;

        $this->assertTrue(self::$model->save());
    }

    /**
     * @method create
     */
    public function testCreate()
    {
        $username = 'darknight-' . rand(0,1000);

        self::$model->create(array('username' => $username));

        $username = self::$model->where('username', $username)->count();

        $this->assertGreaterThan(0,$username);
    }

    /**
     * @method firstOrCreate
     */
    public function testFirstOrCreate()
    {
        $username = 'darknight--' . rand(0,1000000);

        $check = self::$model->where('username', $username)->count();
        $this->assertEquals(0, $check);

        self::$model->firstOrCreate(array('username' => $username));
        $check = self::$model->where('username', $username)->count();
        $this->assertGreaterThan(0, $check);
    }

    /**
     * @method update
     */
    public function testUpdate()
    {
        $totalStatus = self::$model->where('status',1)->count();

        $result = self::$model->where('age', '$gt', 17)->update(array('status' => 1));
        $this->assertTrue($result);

        $newTotalStatus = self::$model->where('status',1)->count();

        $this->assertGreaterThan($totalStatus, $newTotalStatus);
    }

    /**
     * @method delete
     */
    public function testDelete()
    {
        self::$model = self::$instance['user'];

        $firstname = 'blablaa-' . rand(0,1000);
        $lastname = 'blablaa-' . rand(0,1001);

        self::$model->firstname = $firstname;
        self::$model->lastname = $lastname;
        self::$model->age = 18;
        self::$model->save();

        $lastId = self::$model->lastId();

        $fname = self::$model->where('firstname', $firstname)->count();
        $this->assertGreaterThan(0, $fname);

        $item = self::$model->find($lastId);
        $this->assertTrue($item->delete());
    }

    /**
     * @method touch
     */
    public function testTouch()
    {
        self::$model = self::$instance['user'];
        $firstData = self::$model->where('updated_at','$ne',null)->first();
        $updatedAt = $firstData->toArray()[0]['updated_at'];
        $this->assertTrue($firstData->touch());
    }


}