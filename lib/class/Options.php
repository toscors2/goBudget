<?php

    /**
     * Created by PhpStorm.
     * User: root
     * Date: 2/16/17
     * Time: 10:19 AM
     */
    class Options {

        public $tender = [];
        /**
         * @var array
         */
        public $type = [];
        public $weekly = [];
        public $monthly = [];
        public $variable = [];
        public $family = [];
        public $category = [];
        private $conn;

        public function __construct() {

            $this->getConn();
            $this->category = $this->setCategory($this->conn);
            $this->tender = $this->setTender($this->conn);
            $this->type = $this->setType($this->conn);
            $this->family = $this->setFamily($this->conn);
            $this->monthly = $this->setMonthly();
            $this->weekly = $this->setWeekly();
            $this->variable = $this->setVariable();

//            var_dump($this->category);

        }

        private function getConn() {
            require('../../lib/cfg/connect.php');
            $this->conn = $conn;
        }

        /**
         * @param $conn mysqli
         */
        private function setCategory($conn) {

            $category = [];

            $typeSQL = $conn->prepare("SELECT catID, catName FROM budget.iCategories ORDER BY catName");
            $typeSQL->execute();
            $typeSQL->store_result();
            $typeSQL->bind_result($catID, $catName);

            while($typeSQL->fetch()) {
                $category[$catID] = $catName;
            }

            return $category;
        }

        /**
         * @param $conn mysqli
         * @return array
         */
        private function setTender($conn) {
            $tender = [];

            $tenderSQL =
                $conn->prepare("SELECT tenderName, tenderCode, balance, tenderID FROM budget.tender ORDER BY tenderName");
            $tenderSQL->execute();
            $tenderSQL->store_result();
            $tenderSQL->bind_result($tenderName, $tenderCode, $balance, $tenderID);

            while($tenderSQL->fetch()) {
                $tender[$tenderID] = ['code' => $tenderCode, 'name' => $tenderName, 'balance' => $balance];
            }

            return $tender;
        }

        /**
         * @param $conn mysqli
         * @return array
         */
        private function setType($conn) {

            $type = [];

            $typeSQL = $conn->prepare("SELECT typeName, typeNick FROM budget.qeTypes ORDER BY typeName");
            $typeSQL->execute();
            $typeSQL->store_result();
            $typeSQL->bind_result($typeName, $typeNick);

            while($typeSQL->fetch()) {
                $type[$typeNick] = $typeName;
            }

            return $type;
        }

        /**
         * @param $conn mysqli
         */
        private function setFamily($conn) {

            $family = [];

            $familySQL = $conn->prepare("SELECT familyName, familyNick FROM budget.family ORDER BY familyName DESC");
            $familySQL->execute();
            $familySQL->store_result();
            $familySQL->bind_result($famName, $famNick);

            while($familySQL->fetch()) {
                $family[$famNick] = $famName;
            }

            return $family;
        }

        private function setMonthly() {

            $options = [];

            for($i = 0; $i < 28; $i++) {

                $day = $i + 1;

                $options[] = $day;
            }

            return $options;
        }

        private function setWeekly() {

            $options = [];

            $options['sat'] = 'Saturday';
            $options['sun'] = 'Sunday';
            $options['mon'] = 'Monday';
            $options['tue'] = 'Tuesday';
            $options['wed'] = 'Wednesday';
            $options['thu'] = 'Thursday';
            $options['fri'] = 'Friday';

            return $options;
        }

        private function setVariable() {

            $option = "<option value=null>Variable Due Date</option>";

            return $option;

        }

        /**
         * @return array
         */
        public function getCategory() {
            return $this->category;
        }

        public function getType() {
            return $this->type;
        }

        public function getTender() {
            return $this->tender;
        }

        public function getFamily() {
            return $this->family;
        }

        public function getCategoryOptions() {

            $options = [];

            foreach($this->category as $catID => $catName) {
                $options[] = "<option class='iCategory' value='$catID'>" . $catName . "</option>";
            }

            return $options;

        }

        public function getTenderOptions() {

            $options = [];

            foreach($this->tender as $id => $info) {
                $options[] =
                    "<option class='tender' data-process='null' value='" . $info['code'] . "'>" . $info['name'] . "-" .
                    $info['code'] .
                    "</option>";
            }

            return $options;

        }

        public function getTenderBalance() {

            $balances = [];

            foreach($this->tender as $id => $info) {
                $balances[$id] = "<div class='fullWidth' style='height:30px;'>
                            <div class='quarterWidth'>" . $info['code'] . "</div>
                            <div class='quarterWidth'>" . $info['name'] . "</div>
                            <div class='quarterWidth'><label><input type='tel' id='" . $info['code'] . "' value='" .
                                 $info['balance'] . "'/></label></div>
                            <div class='quarterWidth'><button data-code='" . $info['code'] . "' class='reconcileBtn'>Update</button></div>
                          </div>";
            }

            return $balances;

        }

        public function getTypeOptions() {

            $options = [];

            foreach($this->type as $typeNick => $typeName) {
                $options[] = "<option class='type' value='$typeName'>" . $typeNick . "</option>";
            }

            return $options;

        }

        public function getFamilyOptions() {

            $options = [];

            foreach($this->family as $famNick => $famName) {
                $options[] = "<option class='family' value='$famNick'>" . $famName . "</option>";
            }

            return $options;

        }

        public function getMonthlyOptions() {

            $options = [];

            foreach($this->monthly as $days) {
                $options[] = "<option value=" . $days . ">" . $days . "</option>";
            }

            return $options;

        }

        public function getWeeklyOptions() {

            $options = [];

            foreach($this->weekly as $id => $day) {
                $options[] = "<option value=" . $id . ">" . $day . "</option>";
            }

            return $options;
        }

        public function getVariableOptions() {

            $options = [];


            $options[] =  $this->variable;

            return $options;

        }

    }