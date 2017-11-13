<?php

/**
 * Implements the 3XCB product. Gives access to the webservices and functionnalities
 *
 *
 * 
 * @method string getCryptVerstion() returns the number of the crypt version
 */
class Top3Payment extends Top3Service
{

    const TOP3_VERSION = "1.0";
    const CRYPT_VERSION = "3.0";

    /**
     * calulates and returns the shipping date thanks to the order date and the delivery time given in param (default delivery time if null given)
     *
     * @param date $datecom
     * @param int $deliverytime delivery time in days
     * @return string
     */
    public function generateDatelivr($datecom, $deliverytime = null)
    {
        if (is_null($deliverytime))
            $deliverytime = $this->getDefaultdeliverytime();
        $date = strtotime($datecom);
        $datelivr = date('Y-m-d', mktime(0, 0, 0, date('m', $date), date('d', $date) + $deliverytime, date('Y', $date)));

        return $datelivr;
    }

    /**
     * generates and returns crypt value
     *
     * @param Top3XMLElement $order
     * @return string
     */
    public function generateCrypt(Top3Control $order)
    {
        switch (self::CRYPT_VERSION) {
            case '2.0':
                return $this->generateCryptV2($order);
                break;
            case '3.0':
                return $this->generateCryptV3($order);
                break;

            default:
                return $this->generateCryptV2($order);
                break;
        }
    }

    /**
     * generates and returns crypt V3 value
     * 
     * @param Top3XMLElement $order
     * @return string
     */
    public function generateCryptV3(Top3Control $order)
    {
        $merchantreference = urlencode($this->getSiteId());
        $amount = urlencode($order->getOneElementByTagName('montant')->nodeValue);
        $email = urlencode($order->getOneElementByTagName('email')->nodeValue);
        $refid = urlencode($order->getOneElementByTagName('refid')->nodeValue);
        $dateliv = urlencode($order->getOneElementByTagName('datelivr')->nodeValue);

        $data = "merchantreference=" . $merchantreference . "&refid=" . $refid . "&montant=" . $amount . "&email=" . $email . "&datelivr=" . $dateliv;

        $crypt = hash_hmac('sha512', $data, $this->getAuthkey());

        return $crypt;
    }

    public function getFormXMLFeed(FianetTop3Control $order, $xmlparams = null, $urlsys = null, $urlcall = null)
    {

        $scripturl = $this->getUrlfrontline();

        $string = "URLCall=" . urlencode($urlcall) . "&URLSys=" . urlencode($urlsys);
        $checksum = hash_hmac('sha512', $string, $this->getAuthKey());

        $form = "<form name='top3form' action='$scripturl' method='post'>";
        $form .= "<input type='hidden' name='CheckSum' value='$checksum'>";
        $form .= "<input type='hidden' name='URLCall' value='$urlcall'>";
        $form .= "<input type='hidden' name='URLSys' value='$urlsys'>";
        $form .= "<input type='hidden' name='XMLInfo' value='$order'>";
        $form .= "<input type='hidden' name='XMLParam' value='" . $xmlparams . "'>";
        $form .= "</form>";

        return $form;
    }

    public function getChecksum($refid, $top3reference, $currentamount, $state, $mode, $event = null)
    {
        if ($mode == 'urlsys') {
            $string = "refid=" . urldecode($refid) . "&top3reference=" . urldecode($top3reference) . "&currentamount=" . urldecode($currentamount) . "&state=" . urldecode($state) . "&event=" . urldecode($event);
        }

        if ($mode == 'urlcall') {
            $string = "Montant=" . urldecode($currentamount) . "&RefID=" . urldecode($refid) . "&Top3Reference=" . urldecode($top3reference) . "&State=" . urldecode($state);
        }

        $checksum = hash_hmac('sha512', $string, $this->getAuthkey());

        return $checksum;
    }

    public function getChecksumXMLFeed($urlcall, $urlsys)
    {

        $string = "URLCall=" . urlencode($urlcall) . "&URLSys=" . urlencode($urlsys);
        $checksum = hash_hmac('sha512', $string, $this->getAuthKey());

        return $checksum;
    }

    public function getEligibility($amount, $country)
    {

        $string = "merchantreference=" . $this->getSiteid() . "&commandamount=" . $amount . "&country=" . $country;

        $checksum = hash_hmac('sha512', $string, $this->getAuthKey());

        $data = array(
            "commandamount" => $amount,
            "country" => $country,
            "checksum" => $checksum
        );

        $con = new Top3Socket($this->getUrleligibility() . $this->getSiteid(), 'POST', $data, true);

        $result = $con->send(true);

        $array_result = array();

        $rep = json_decode($result['response'], true);

        $array_result['http_code'] = getHttpResponse($result['header']);

        if ($array_result['http_code'] == 200) {
            $array_result['top3_code'] = $rep['code'];
            $array_result['top3_libelle'] = $rep['libelle'];

            if ($array_result['top3_code'] == 'OK') {

                $array_result['result'] = true;
            } else {

                $array_result['result'] = false;
            }
        } else {
            $array_result['top3_code'] = null;
            $array_result['top3_libelle'] = null;
            $array_result['result'] = false;
        }

        return $array_result;
    }

    public function getTransactionByRefID($top3reference, $refid = '')
    {

        $string = "merchantreference=" . $this->getSiteid() . "&top3reference=" . $top3reference . "&refid=" . $refid;

        $checksum = hash_hmac('sha512', $string, $this->getAuthKey());

        $data = array("checksum" => $checksum);

        $con = new Top3Socket($this->getUrlgettransaction() . $this->getSiteid() . '/top3reference/' . $top3reference, 'POST', $data, true);

        $result = $con->send(true);

        $array_result = array();

        $rep = json_decode($result['response'], true);

        $array_result['http_code'] = getHttpResponse($result['header']);

        if ($array_result['http_code'] == 200) {
            $array_result['xmlparam'] = $rep['xmlparam'];
            $array_result['top3reference'] = $rep['top3reference'];
            $array_result['currentamount'] = $rep['currentamount'];
            $array_result['state'] = $rep['state'];
            $array_result['event'] = $rep['event'];
            $array_result['checksum'] = $rep['checksum'];
            $array_result['result'] = true;
        } else {
            $array_result['xmlparam'] = null;
            $array_result['top3reference'] = null;
            $array_result['currentamount'] = null;
            $array_result['state'] = null;
            $array_result['event'] = null;
            $array_result['checksum'] = null;
            $array_result['result'] = false;
        }
        return $array_result;
    }

    public function sendRemoteControl($action, $top3reference, $final_amount = '')
    {
        $array_result = array();

        if (!in_array($action, array('validatetransaction', 'partiallyvalidatetransaction', 'canceltransaction', 'cancelprecedentorder'))) {
            $array_result['top3_code'] = null;
            $array_result['top3_libelle'] = null;
            $array_result['result'] = false;
            return $array_result;
        }

        $string = "merchantreference=" . urlencode($this->getSiteid()) . "&top3reference=" . urlencode($top3reference);
        if ($action == 'partiallyvalidatetransaction')
            $string .= "&montantfinal=" . urlencode($final_amount);

        $string .= "&action=" . urlencode($action);

        $checksum = hash_hmac('sha512', $string, $this->getAuthKey());

        $data = array("montantfinal" => $final_amount, "checksum" => $checksum);

        $url = "getUrl$action";

        $con = new Top3Socket($this->$url() . $this->getSiteid() . '/top3reference/' . $top3reference, 'POST', $data, true);

        $result = $con->send(true);

        $rep = json_decode($result['response'], true);

        $array_result['http_code'] = getHttpResponse($result['header']);

        if ($array_result['http_code'] == 200) {
            $array_result['top3_code'] = $rep['code'];
            $array_result['top3_libelle'] = $rep['libelle'];

            if ($array_result['top3_code'] == 'OK') {
                $array_result['result'] = true;
            } else {
                $array_result['result'] = false;
            }
        } else {
            $array_result['top3_code'] = null;
            $array_result['top3_libelle'] = null;
            $array_result['result'] = false;
        }

        return $array_result;
    }

}
