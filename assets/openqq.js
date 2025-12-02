function openQQOrTIM() {
    // 目标QQ号
    const targetUin = "442834517";
    // 设备判断：是否为移动端
    const isMobile = /Android|iPhone|iPad|iPod/i.test(navigator.userAgent);

    let唤起协议 = "";
    let下载链接 = "";

    if (isMobile) {
        // 移动端：优先唤起手机QQ（包名com.tencent.mobileqq），次优先TIM
        唤起协议 = `mqqapi://card/show_pslcard?src_type=internal&version=1&uin=${targetUin}&card_type=friend&source=qrcode`;
        下载链接 = "https://im.qq.com/index/"; // 手机QQ下载页
    } else {
        // 电脑端：同时兼容QQ和TIM（共用AddContact协议，系统会自动调用已安装的客户端）
        唤起协议 = `tencent://AddContact/?fromId=45&fromSubId=1&subcmd=all&uin=${targetUin}&website=qzone`;
        下载链接 = "https://im.qq.com/pcqq/index.shtml"; // 电脑端QQ/TIM下载页
    }

    // 尝试唤起客户端
    window.location.href = 唤起协议;
}