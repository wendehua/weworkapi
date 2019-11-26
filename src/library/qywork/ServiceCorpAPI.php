<?php
namespace api\qywork;

use api\qywork\utils\Utils;
use api\qywork\utils\HttpUtils;
use api\qywork\model\SetSessionInfoReq;
use api\qywork\model\GetPermanentCodeRsp;
use api\qywork\model\GetAuthInfoRsp;
use api\qywork\model\GetAdminListRsp;
use api\qywork\model\GetUserinfoBy3rdRsp;
use api\qywork\model\GetUserDetailBy3rdRsp;

/*
 * Copyright (C) 2017 All rights reserved.
 *
 * @File ServiceCorpAPI.class.php
 * @Brief : Ϊ�����̿��ŵĽӿ�, ʹ��Ӧ����Ȩ��token
 * @Author abelzhu, abelzhu@tencent.com
 * @Version 1.0
 * @Date 2017-12-26
 *
 */
class ServiceCorpAPI extends CorpAPI
{
    private $suite_id = null; // string
    private $suite_secret = null; // string
    private $suite_ticket = null; // string
    
    private $authCorpId = null; // string
    private $permanentCode = null; // string
    
    private $suiteAccessToken = null; // string
    
    public function __construct(
        $suite_id=null,
        $suite_secret=null,
        $suite_ticket=null,
        $authCorpId=null,
        $permanentCode=null)
    {
        $this->suite_id = $suite_id;
        $this->suite_secret = $suite_secret;
        $this->suite_ticket = $suite_ticket;
        
        // ���� CorpAPI ��function�� ��Ҫ��������������
        $this->authCorpId = $authCorpId;
        $this->permanentCode = $permanentCode;
    }
    
    /**
     * @brief RefreshAccessToken : override CorpAPI�ĺ�����ʹ�����������̵�get_corp_token
     *
     * @return : string
     */
    protected function RefreshAccessToken()
    {
        Utils::checkNotEmptyStr($this->authCorpId, "auth_corpid");
        Utils::checkNotEmptyStr($this->permanentCode, "permanent_code");
        $args = array(
            "auth_corpid" => $this->authCorpId,
            "permanent_code" => $this->permanentCode
        );
        $url = HttpUtils::MakeUrl("/cgi-bin/service/get_corp_token?suite_access_token=SUITE_ACCESS_TOKEN");
        $this->_HttpPostParseToJson($url, $args, false);
        $this->_CheckErrCode();
        
        $this->accessToken = $this->rspJson["access_token"];
    }
    
    /**
     * @brief GetSuiteAccessToken : ��ȡ������Ӧ��ƾ֤
     *
     * @link https://work.weixin.qq.com/api/doc#10975/��ȡ������Ӧ��ƾ֤
     *
     * @note �����߲��ù��ģ�������Զ���ȡ������
     *
     * @return : string
     */
    protected function GetSuiteAccessToken()
    {
        if ( ! Utils::notEmptyStr($this->suiteAccessToken)) {
            $this->RefreshSuiteAccessToken();
        }
        return $this->suiteAccessToken;
    }
    protected function RefreshSuiteAccessToken()
    {
        Utils::checkNotEmptyStr($this->suite_id, "suite_id");
        Utils::checkNotEmptyStr($this->suite_secret, "suite_secret");
        Utils::checkNotEmptyStr($this->suite_ticket, "suite_ticket");
        $args = array(
            "suite_id" => $this->suite_id,
            "suite_secret" => $this->suite_secret,
            "suite_ticket" => $this->suite_ticket,
        );
        $url = HttpUtils::MakeUrl("/cgi-bin/service/get_suite_token");
        $this->_HttpPostParseToJson($url, $args, false);
        $this->_CheckErrCode();
        
        $this->suiteAccessToken= $this->rspJson["suite_access_token"];
    }
    
    // ---------------------- ���������Žӿ� ----------------------------------
    //
    //
    /**
     * @brief GetPreAuthCode : ��ȡԤ��Ȩ��
     *
     * @link https://work.weixin.qq.com/api/doc#10975/��ȡԤ��Ȩ��
     *
     * @return : string pre_auth_code
     */
    public function GetPreAuthCode()
    {
        self::_HttpCall(self::GET_PRE_AUTH_CODE, 'GET', null);
        return $this->rspJson["pre_auth_code"];
    }
    
    /**
     * @brief SetSessionInfo : ������Ȩ����
     *
     * @link https://work.weixin.qq.com/api/doc#10975/������Ȩ����
     *
     * @param $SetSessionInfoReq
     */
    public function SetSessionInfo( SetSessionInfoReq $SetSessionInfoReq)
    {
        $args = $SetSessionInfoReq->FormatArgs();
        self::_HttpCall(self::SET_SESSION_INFO, 'POST', $args);
    }
    
    /**
     * @brief GetPermanentCode : ��ȡ��ҵ������Ȩ��
     *
     * @link https://work.weixin.qq.com/api/doc#10975/��ȡ��ҵ������Ȩ��
     *
     * @param $temp_auth_code : string ��ʱ��Ȩ��
     *
     * @return : GetPermanentCodeRsp
     */
    public function GetPermanentCode($temp_auth_code)
    {
        $args = array("auth_code" => $temp_auth_code);
        self::_HttpCall(self::GET_PERMANENT_CODE, 'POST', $args);
        return GetPermanentCodeRsp::ParseFromArray($this->rspJson);
    }
    
    /**
     * @brief GetAuthInfo : ��ȡ��ҵ��Ȩ��Ϣ
     *
     * @link https://work.weixin.qq.com/api/doc#10975/��ȡ��ҵ��Ȩ��Ϣ
     *
     * @param $auth_corpid : string
     * @param $permanent_code : ������Ȩ��
     *
     * @return : GetAuthInfoRsp
     */
    public function GetAuthInfo($auth_corpid, $permanent_code)
    {
        Utils::checkNotEmptyStr($auth_corpid, "auth_corpid");
        Utils::checkNotEmptyStr($permanent_code, "permanent_code");
        $args = array(
            "auth_corpid" => $auth_corpid,
            "permanent_code" => $permanent_code
        );
        self::_HttpCall(self::GET_AUTH_INFO, 'POST', $args);
        return  GetAuthInfoRsp::ParseFromArray($this->rspJson);
    }
    
    /**
     * @brief GetAdminList : ��ȡӦ�õĹ���Ա�б�
     *
     * @link https://work.weixin.qq.com/api/doc#10975/��ȡӦ�õĹ���Ա�б�
     *
     * @param $auth_corpid : string
     * @param $agentid : uint
     *
     * @return  : GetAdminListRsp
     */
    public function GetAdminList($auth_corpid, $agentid)
    {
        Utils::checkNotEmptyStr($auth_corpid, "auth_corpid");
        Utils::checkIsUInt($agentid, "agentid");
        $args = array(
            "auth_corpid" => $auth_corpid,
            "agentid" => $agentid
        );
        self::_HttpCall(self::GET_ADMIN_LIST, 'POST', $args);
        return GetAdminListRsp::ParseFromArray($this->rspJson);
    }
    
    /**
     * @brief GetUserinfoBy3rd :����������code��ȡ��ҵ��Ա��Ϣ
     *
     * @link https://work.weixin.qq.com/api/doc#10975/����������code��ȡ��ҵ��Ա��Ϣ
     *
     * @param $code : string
     *
     * @return  : GetUserinfoBy3rdRsp
     */
    public function GetUserinfoBy3rd($code)
    {
        self::_HttpCall(self::GET_USER_INFO_BY_3RD, 'GET', array('code'=>$code));
        return GetUserinfoBy3rdRsp::ParseFromArray($this->rspJson);
    }
    
    /**
     * @brief GetUserDetailBy3rd : ������ʹ��user_ticket��ȡ��Ա����
     *
     * @link https://work.weixin.qq.com/api/doc#10975/������ʹ��user_ticket��ȡ��Ա����
     *
     * @param $user_ticket : string
     *
     * @return  : GetUserDetailBy3rdRsp
     */
    public function GetUserDetailBy3rd($user_ticket)
    {
        Utils::checkNotEmptyStr($user_ticket, "user_ticket");
        $args = array("user_ticket" => $user_ticket);
        self::_HttpCall(self::GET_USER_DETAIL_BY_3RD, 'POST', $args);
        return  GetUserDetailBy3rdRsp::ParseFromArray($this->rspJson);
    }
}

