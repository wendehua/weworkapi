<?php
/*
 * Copyright (C) 2017 All rights reserved.
 *
 * @File ServiceProviderAPI.class.php
 * @Brief : Ϊ�����̿��ŵĽӿ�, ʹ�÷����̵�token
 * @Author abelzhu, abelzhu@tencent.com
 * @Version 1.0
 * @Date 2017-12-26
 *
 */
namespace api\qywork;

use api\qywork\utils\Utils;
use api\qywork\utils\HttpUtils;
use api\qywork\model\GetLoginInfoRsp;
use api\qywork\model\GetRegisterCodeReq;
use api\qywork\model\GetRegisterInfoRsp;
use api\qywork\model\SetAgentScopeReq;
use api\qywork\model\SetAgentScopeRsp;

class ServiceProviderAPI extends AbsAPI
{
    private $corpid = null; // string
    private $provider_secret = null; // string
    private $provider_access_token = null; // string
    
    /**
     * ����SetAgentScope/SetContactSyncSuccess �����ӿڿ��Բ��ô�corpid/provider_secret
     */
    public function __construct($corpid=null, $provider_secret=null)
    {
        $this->corpid = $corpid;
        $this->provider_secret = $provider_secret;
    }
    
    protected function GetProviderAccessToken()
    {
        if ( ! Utils::notEmptyStr($this->provider_access_token)) {
            $this->RefreshProviderAccessToken();
        }
        return $this->provider_access_token;
    }
    protected function RefreshProviderAccessToken()
    {
        Utils::checkNotEmptyStr($this->corpid, "corpid");
        Utils::checkNotEmptyStr($this->provider_secret, "provider_secret");
        
        $args = array(
            "corpid" => $this->corpid,
            "provider_secret" => $this->provider_secret
        );
        $url = HttpUtils::MakeUrl("/cgi-bin/service/get_provider_token");
        $this->_HttpPostParseToJson($url, $args, false);
        $this->_CheckErrCode();
        
        $this->provider_access_token = $this->rspJson["provider_access_token"];
    }
    
    // ------------------------- �����¼ -------------------------------------
    //
    
    /**
     * @brief GetLoginInfo : ��ȡ��¼�û���Ϣ
     *
     * @link https://work.weixin.qq.com/api/doc#10991/��ȡ��¼�û���Ϣ
     *
     * @param $auth_code : string
     *
     * @return : GetLoginInfoRsp
     */
    public function GetLoginInfo($auth_code)
    {
        Utils::checkNotEmptyStr($auth_code, "auth_code");
        $args = array("auth_code" => $auth_code);
        self::_HttpCall(self::GET_LOGIN_INFO, 'POST', $args);
        return  GetLoginInfoRsp::ParseFromArray($this->rspJson);
    }
    
    // ------------------------- ע�ᶨ�ƻ� -----------------------------------
    //
    /**
     * @brief GetRegisterCode : ��ȡע����
     *
     * @link https://work.weixin.qq.com/api/doc#11729/��ȡע����
     *
     * @param $GetRegisterCodeReq
     *
     * @return : string register_code
     */
    public function GetRegisterCode(GetRegisterCodeReq $GetRegisterCodeReq)
    {
        $args = $GetRegisterCodeReq->FormatArgs();
        self::_HttpCall(self::GET_REGISTER_CODE, 'POST', $args);
        return $this->rspJson["register_code"];
    }
    
    /**
     * @brief GetRegisterInfo : ��ѯע��״̬
     *
     * @link https://work.weixin.qq.com/api/doc#11729/��ѯע��״̬
     *
     * @param $register_code : string
     *
     * @return : GetRegisterInfoRsp
     */
    public function GetRegisterInfo($register_code)
    {
        Utils::checkNotEmptyStr($register_code, "register_code");
        $args = array("register_code" => $register_code);
        self::_HttpCall(self::GET_REGISTER_INFO, 'POST', $args);
        return GetRegisterInfoRsp::ParseFromArray($this->rspJson);
    }
    
    /**
     * @brief SetAgentScope : ������ȨӦ�ÿɼ���Χ
     *
     * @link https://work.weixin.qq.com/api/doc#11729/������ȨӦ�ÿɼ���Χ
     *
     * @param $access_token : �ýӿ�ֻ��ʹ��ע����ɻص��¼����߲�ѯע��״̬���ص�access_token
     * @param $SetAgentScopeReq : SetAgentScopeReq
     *
     * @return : SetAgentScopeRsp
     */
    public function SetAgentScope($access_token, SetAgentScopeReq $SetAgentScopeReq)
    {
        $args = $SetAgentScopeReq->FormatArgs();
        self::_HttpCall(self::SET_AGENT_SCOPE."?access_token={$access_token}", 'POST', $args);
        return  SetAgentScopeRsp::ParseFromArray($this->rspJson);
    }
    
    /**
     * @brief SetContactSyncSuccess : ����ͨѶ¼ͬ�����
     *
     * @link https://work.weixin.qq.com/api/doc#11729/����ͨѶ¼ͬ�����
     *
     * @param $access_token : �ýӿ�ֻ��ʹ��ע����ɻص��¼����߲�ѯע��״̬���ص�access_token
     */
    public function SetContactSyncSuccess($access_token)
    {
        self::_HttpCall(self::SET_CONTACT_SYNC_SUCCESS."?access_token={$access_token}", 'GET', null);
    }
}

