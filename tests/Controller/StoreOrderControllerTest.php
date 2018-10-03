<?php
use Liip\FunctionalTestBundle\Test\WebTestCase;

class StoreOrderControllerTest extends WebTestCase
{

    /**
     * GET the empty storeOrders
     */
    public function testGetEmptyStoreOrders()
    {
        $this->loadFixtures();
        $client = $this->makeClient();
        $client->request('GET', '/v1/order');
        $this->assertEmpty(json_decode($client->getResponse()->getContent()));
        $this->assertStatusCode(200, $client);
        $this->assertTrue(
            $client->getResponse()->headers->contains(
                'Content-Type',
                'application/json'
            )
        );
        $this->assertTrue(
            $client->getResponse()->headers->contains(
                'Allow',
                'GET, POST'
            )
        );
    }


    /**
     * GET the populated storeOrders
     */
    public function testGetPopulatedStoreOrders()
    {
        $this->loadFixtures([
            'App\Fixture\Test\StoreOrderFixture'
        ]);

        $client = $this->makeClient();
        $client->request('GET', '/v1/order');
        $storeOrders = json_decode($client->getResponse()->getContent());
        $this->assertEquals(count($storeOrders), 2);
        $this->assertTrue(
            $client->getResponse()->headers->contains(
                'Content-Type',
                'application/json'
            )
        );
        $this->assertTrue(
            $client->getResponse()->headers->contains(
                'Allow',
                'GET, POST'
            )
        );

        foreach ($storeOrders as $key => $storeOrder) {
            $this->assertEquals('CH'.$key, $storeOrder->country);
            $this->assertEquals('testName'.$key, $storeOrder->fullname);
            $this->assertEquals('testStreet'.$key, $storeOrder->street);
            $this->assertEquals('testZip'.$key, $storeOrder->zip);
        }

        $this->assertEquals(3, count($storeOrders[0]->products));
        foreach ($storeOrders[0]->products as $key => $product) {
            $this->assertEquals('product'.$key, $product->title);
        }

        $this->assertEquals(20, count($storeOrders[1]->products));
        foreach ($storeOrders[1]->products as $key => $product) {
            $this->assertEquals('product'.$key, $product->title);
        }

        $this->assertStatusCode(200, $client);

    }

    /**
     * POST a storeOrder
     */
    public function testPostStoreOrder()
    {
        $this->loadFixtures([
            'App\Fixture\Test\StoreOrderFixture'
        ]);

        $client = $this->makeClient();
        $storeOrderJson = '{
            "street": "testStreet",
            "zip": "testZip",
            "fullname": "testFullname",
            "country": "testCountry",
            "paid": false,
            "products": [
                {
                  "id": 1
                },
                {
                  "id": 2
                }
            ]
        }';

        $client->request(
            'POST',
            '/v1/order',
            [],
            [],
            array('CONTENT_TYPE' => 'application/json'),
            $storeOrderJson
        );

        $this->assertStatusCode(201, $client);
        $this->assertTrue(
            $client->getResponse()->headers->contains(
                'Content-Type',
                'application/json'
            )
        );
        $this->assertTrue(
            $client->getResponse()->headers->contains(
                'Allow',
                'GET, POST'
            )
        );

        $storeOrder = json_decode($client->getResponse()->getContent());
        $this->assertEquals(3, $storeOrder->id);
        $this->assertEquals('testStreet', $storeOrder->street);
        $this->assertEquals('testZip', $storeOrder->zip);
        $this->assertEquals('testFullname', $storeOrder->fullname);
        $this->assertEquals('testCountry', $storeOrder->country);
        $this->assertEquals(2, count($storeOrder->products));
        $this->assertEquals(1, $storeOrder->products[0]->id);
        $this->assertEquals(2, $storeOrder->products[1]->id);
    }

    /**
     * POST a storeOrder with error
     */
    public function testPostStoreOrderValidation()
    {
        $this->loadFixtures([
            'App\Fixture\Test\StoreOrderFixture'
        ]);

        $client = $this->makeClient();
        $storeOrderJson = '{
            
        }';
        $this->postStoreOrder($storeOrderJson, $client);

        $this->assertStatusCode(400, $client);
        $this->assertTrue(
            $client->getResponse()->headers->contains(
                'Content-Type',
                'application/json'
            )
        );
        $this->assertTrue(
            $client->getResponse()->headers->contains(
                'Allow',
                'GET, POST'
            )
        );

        $error = json_decode($client->getResponse()->getContent());
        $this->assertEquals(4, count($error));
        $this->assertEquals('street', $error[0]->property_path);
        $this->assertEquals('This value should not be blank.', $error[0]->message);
        $this->assertEquals('zip', $error[1]->property_path);
        $this->assertEquals('This value should not be blank.', $error[1]->message);
        $this->assertEquals('fullname', $error[2]->property_path);
        $this->assertEquals('This value should not be blank.', $error[2]->message);
        $this->assertEquals('country', $error[3]->property_path);
        $this->assertEquals('This value should not be blank.', $error[3]->message);
    }
    
//
//    /**
//     * PUT a storeOrder
//     */
//    public function testPutStoreOrder()
//    {
//        $this->loadFixtures([
//            'App\Fixture\Test\StoreOrderFixture'
//        ]);
//        $client = $this->makeClient();
//
//        $storeOrderJson = '{
//            "title": "newTitle",
//            "description": "newDescription",
//            "imgUrl": "https://img.url/test.png",
//            "price": 33,
//            "quantity": 45
//        }';
//
//        $this->putStoreOrder(1, $storeOrderJson, $client);
//        $this->assertStatusCode(200, $client);
//        $this->assertTrue(
//            $client->getResponse()->headers->contains(
//                'Content-Type',
//                'application/json'
//            )
//        );
//        $this->assertTrue(
//            $client->getResponse()->headers->contains(
//                'Allow',
//                'GET, PUT, DELETE'
//            )
//        );
//
//        $updatedStoreOrder = json_decode($client->getResponse()->getContent());
//        $this->assertEquals(1, $updatedStoreOrder->id);
//        $this->assertEquals('newTitle', $updatedStoreOrder->title);
//        $this->assertEquals('newDescription', $updatedStoreOrder->description);
//        $this->assertEquals('https://img.url/test.png', $updatedStoreOrder->imgUrl);
//        $this->assertEquals(33, $updatedStoreOrder->price);
//        $this->assertEquals(45, $updatedStoreOrder->quantity);
//
//        $client->request('GET', '/v1/order/1');
//        $storeOrder = json_decode($client->getResponse()->getContent());
//        $this->assertEquals($storeOrder->id, $updatedStoreOrder->id);
//        $this->assertEquals($storeOrder->title, $updatedStoreOrder->title);
//        $this->assertEquals($storeOrder->description, $updatedStoreOrder->description);
//        $this->assertEquals($storeOrder->imgUrl, $updatedStoreOrder->imgUrl);
//        $this->assertEquals($storeOrder->price, $updatedStoreOrder->price);
//        $this->assertEquals($storeOrder->quantity, $updatedStoreOrder->quantity);
//    }
//
//    /**
//     * PUT a storeOrder with validation errors
//     */
//    public function testPutStoreOrderValidation()
//    {
//        $this->loadFixtures([
//            'App\Fixture\Test\StoreOrderFixture'
//        ]);
//        $client = $this->makeClient();
//
//        $storeOrderJson = '{
//           
//        }';
//
//        $this->putStoreOrder(1, $storeOrderJson, $client);
//        $this->assertStatusCode(400, $client);
//        $this->assertTrue(
//            $client->getResponse()->headers->contains(
//                'Content-Type',
//                'application/json'
//            )
//        );
//        $this->assertTrue(
//            $client->getResponse()->headers->contains(
//                'Allow',
//                'GET, PUT, DELETE'
//            )
//        );
//
//        $error = json_decode($client->getResponse()->getContent());
//        $this->assertEquals(5, count($error->violations));
//        $this->assertEquals('title', $error->violations[0]->property_path);
//        $this->assertEquals('This value should not be blank.', $error->violations[0]->title);
//        $this->assertEquals('description', $error->violations[1]->property_path);
//        $this->assertEquals('This value should not be blank.', $error->violations[1]->title);
//        $this->assertEquals('imgUrl', $error->violations[2]->property_path);
//        $this->assertEquals('This value should not be blank.', $error->violations[2]->title);
//        $this->assertEquals('price', $error->violations[3]->property_path);
//        $this->assertEquals('This value should not be blank.', $error->violations[3]->title);
//        $this->assertEquals('quantity', $error->violations[4]->property_path);
//        $this->assertEquals('This value should not be blank.', $error->violations[4]->title);
//
//        $this->postStoreOrder('{"imgUrl": "notARealUrl"}', $client);
//        $error = json_decode($client->getResponse()->getContent());
//        $this->assertEquals(5, count($error->violations));
//        $this->assertEquals('imgUrl', $error->violations[2]->property_path);
//        $this->assertEquals('This value is not a valid URL.', $error->violations[2]->title);
//    }
//
//    /**
//     * DELETE a storeOrder
//     */
//    public function testDeleteStoreOrder()
//    {
//        $this->loadFixtures([
//            'App\Fixture\Test\StoreOrderFixture'
//        ]);
//        $client = $this->makeClient();
//        $client->request(
//            'DELETE',
//            '/v1/order/1',
//            [],
//            [],
//            array('CONTENT_TYPE' => 'application/json'),
//            null
//        );
//        $this->assertStatusCode(204, $client);
//        $this->assertTrue(
//            $client->getResponse()->headers->contains(
//                'Allow',
//                'GET, PUT, DELETE'
//            )
//        );
//
//        $client->request('GET', '/v1/order');
//        $storeOrders = json_decode($client->getResponse()->getContent());
//        $this->assertEquals(count($storeOrders), 19);
//        $this->assertEquals(2, $storeOrders[0]->id);
//    }
//
//
    /**
     * POST a storeOrder
     * @param string $body
     * @param $client
     */
    private function postStoreOrder(string $body, $client) {
        $client->request(
            'POST',
            '/v1/order',
            [],
            [],
            array('CONTENT_TYPE' => 'application/json'),
            $body
        );
    }

    /**
     * PUT a storeOrder
     * @param $id
     * @param string $body
     * @param $client
     */
    private function putStoreOrder(int $id,string $body, $client) {
        $client->request(
            'PUT',
            '/v1/order/' . $id,
            [],
            [],
            array('CONTENT_TYPE' => 'application/json'),
            $body
        );
    }
}