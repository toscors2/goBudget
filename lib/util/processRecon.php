<?php
    /**
     * Created by PhpStorm.
     * User: root
     * Date: 2/11/17
     * Time: 12:33 PM
     */

    session_start();
    include('../cfg/connect.php');
    define("BR", "</br>");

    $data = $info = $message = [];

    $info = ['entryID' => null, 'reconID' => null, 'divID' => null, 'action' => null, 'found' => null];

    /**
     * @param $entryID
     * @return string
     * @param $conn mysqli
     */
    function getSource($entryID, $conn) {

        $getSource = $conn->prepare("
SELECT c.sourceID FROM budget.quickEntry AS a 
LEFT JOIN budget.lineItems AS b ON a.transID = b.transID
LEFT JOIN budget.sources AS c ON b.iSource = c.sourceName 
WHERE a.entryID = ?");
        $getSource->bind_param("s", $entryID);
        $getSource->execute();
        $getSource->store_result();
        $getSource->bind_result($iSource);
        $getSource->fetch();

        return $iSource;

    }

    /**
     * @param $source
     * @param $reconID
     * @param $entryID
     * @param $conn mysqli
     */
    function reconcileFound($source, $reconID, $entryID, $conn) {

        $updateRecon =
            $conn->prepare("UPDATE budget.recon SET reconSource = ?, reconStatus = TRUE, entryID = ? WHERE reconID = ?");
        $updateRecon->bind_param("sss", $source, $entryID, $reconID);
        $updateRecon->execute();

        !$updateRecon
            ? $message = 'error updating found recon in recon table'
            : $message = 'updated this found transaction from recon table';

        return $message;
    }

    /**
     * @param $reconID
     * @param $conn mysqli
     */
    function deleteFound($reconID, $conn) {
        $removeRecon = $conn->prepare("DELETE FROM budget.recon WHERE reconID = ?");
        $removeRecon->bind_param("s", $reconID);
        $removeRecon->execute();

        !$removeRecon
            ? $message = 'error deleting found transactions from recon table'
            : $message = 'trans deleted from recon table';

        return $message;

    }

    /**
     * @param $entryID
     * @param $conn mysqli
     */
    function reconcileQE($entryID, $conn) {
        $updateQE = $conn->prepare("UPDATE budget.quickEntry SET reconciled = TRUE WHERE entryID = ?");
        $updateQE->bind_param("s", $entryID);
        $updateQE->execute();

        !$updateQE
            ? $message = 'error updating quick entry'
            : $message = 'quick entry marked reconciled';

        return $message;
    }

    /**
     * @param $source
     * @param $reconID
     * @param $entryID
     * @param $conn mysqli
     */
    function saveNotFound($reconID, $conn) {

        $updateRecon =
            $conn->prepare("UPDATE budget.recon SET reconSource = 'NOT MATCHED', reconStatus = TRUE, entryID = 'NOT FOUND', reconID = ?");
        $updateRecon->bind_param("s", $reconID);
        $updateRecon->execute();

        !$updateRecon
            ? $message = 'error saving not found transaction in recon table'
            : $message = 'updated not found transaction in recon table';

        return $message;
    }

    /**
     * @param $reconID
     * @param $conn mysqli
     */
    function deleteNotFound($reconID, $conn) {
        $removeRecon = $conn->prepare("DELETE FROM budget.recon WHERE reconID = ?");
        $removeRecon->bind_param("s", $reconID);
        $removeRecon->execute();

        !$removeRecon
            ? $message = 'error deleting not found transactions from recon table'
            : $message = 'deleted this not found transaction from recon table';

        return $message;
    }

    /**
     * @param $info
     * @param $conn mysqli
     * @return array
     */
    function processNotFoundTrans($info, $conn) {

        $message = [];

        if($info->action == 'recon') {

//            $message[] = 'notFound reconciled';

                $message[] = saveNotFound($info->reconID, $conn);
        } else {

//            $message[] = 'notFound deleted';

                $message[] = deleteNotFound($info->reconID, $conn);
        }

        return $message;
    }

    /**
     * @param $info
     * @param $conn mysqli
     * @return array
     */
    function processFoundTrans($info, $conn) {

        $message = [];

        if($info->action == 'recon') {
            $source = getSource($info->entryID, $conn);

//            $message[] = 'found reconciled';

                $message[] = reconcileFound($source, $info->reconID, $info->entryID, $conn);
                $message[] = reconcileQE($info->entryID, $conn);
        } else {

//            $message[] = 'found deleted';

                $message[] = deleteFound($info->reconID, $conn);
                $message[] = reconcileQE($info->entryID, $conn);
        }

        return $message;
    }


    isset ($_POST) ? $sentData = $_POST : $sentData = null;

    if($sentData != null) {

        foreach($sentData as $key => $value) {

//            echo $key . ': value: ' . $value . BR;

            $info[$key] = $value;
        }

        $info = json_decode(json_encode($info));

//        echo $info->found;

        if($info->found == true) {

            $message[] = processFoundTrans($info, $conn);
        } else {
            $message[] = processNotFoundTrans($info, $conn);
        }

        $data['message'] = $message;
        $data['divID'] = $info->divID;

    }

    echo json_encode($data);