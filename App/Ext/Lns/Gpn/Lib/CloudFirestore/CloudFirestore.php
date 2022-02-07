<?php
namespace Lns\Gpn\Lib\CloudFirestore;

/* IMPORTANT DATA TYPES
stringValue
doubleValue
integerValue
booleanValue
arrayValue
bytesValue
geoPointValue
mapValue
nullValue
referenceValue
timestampValue */
    
class CloudFirestore {
    
    protected $_password;

    public function __construct(
        \Lns\Sb\Lib\Password\Password $Password
    ) {
        $this->_password = $Password;
    }
    public function save($receiver_id, $sender_name, $message, $collection, $project_id, $firestore_key, $data = '') {

        /* $unique_id = $this->_password->generate(25);

        $firestore_data  = [
            "receiver_id" => ["stringValue" => $receiver_id],
            "sender_name" => ["stringValue" => $sender_name],
            "message" => ["stringValue" => $message],
            "collection" => ["stringValue" => $collection],
            "document_id" => ["stringValue" => $unique_id],
            "data" => ["stringValue" => $data]
        ];
        $data = ["fields" => (object)$firestore_data];
    
        $json = json_encode($data);
        
        $url = "https://firestore.googleapis.com/v1beta1/projects/gapan-internal-app/databases/(default)/documents/".$collection."/".$unique_id;

        $curl = curl_init();
        curl_setopt_array($curl, array(
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_HTTPHEADER => array('Content-Type: application/json',
                'Content-Length: ' . strlen($json),
                'X-HTTP-Method-Override: PATCH'),
            CURLOPT_URL => $url . '?key='.$firestore_key,
            CURLOPT_USERAGENT => 'cURL',
            CURLOPT_POSTFIELDS => $json
        ));
        $response = curl_exec($curl);
        curl_close($curl); */
    }
}
?>