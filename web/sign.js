let check_box1 = document.getElementById("store_and_sign_for_tomorrow");
let check_box2 = document.getElementById("start_right_now");
let message_box = document.getElementById("msg_box");
let button = document.getElementById("submit_button");

function check_state() {
    switch ((check_box1.checked ? 1 : 0) + (check_box2.checked ? 2 : 0)) {
        case 0:
            button.setAttribute("disabled", "true");
            message_box.innerText = "";
            break;
        case 1:
            button.removeAttribute("disabled")
            message_box.innerText = "保存数据, 从明天起每日签到";
            break;
        case 2:
            button.removeAttribute("disabled")
            message_box.innerText = "现在立刻签到, 虽然BUSS会发送给服务器但不会储存";
            break;
        case 3:
            button.removeAttribute("disabled")
            message_box.innerText = "储存数据每天早上签到, 并立刻开始签到";
            break;
        default:
            console.log("别闹");
            break;
    }
}

function return_code(code, str) {
    switch (String(code)) {
        case '2001':
            message_box.innerText = str;
            break;
        case '2002':
            message_box.innerText = "已完成, 耗时" + str + "秒";
            break;
        case '504':
            message_box.innerText = "签到失败, 检查bduss";
            console.log(str);
            break;
        default:
            message_box.innerText = "未知错误";
            console.log(str);
            break;
    }
}

function submit() {
    let name = document.getElementById("sfsbf").value;
    let bduss = document.getElementById("bduss").value;
    let function_num = (check_box1.checked ? 1 : 0) + (check_box2.checked ? 2 : 0);

    let formData = new FormData();
    formData.append('nname', name);
    formData.append('bduss', bduss);
    formData.append('function_num', function_num);

    const Http = new XMLHttpRequest();
    Http.open("POST", 'rest.php');
    Http.send(formData);
    message_box.innerText = "数据已发送, 任务正在进行, 可以关闭浏览器, 不会造成影响";

    Http.onload = function () {
        let jsonText = Http.responseText;
        let result;
        try {
            result = JSON.parse(decodeURIComponent(jsonText));
            if (result.hasOwnProperty("code") && result.hasOwnProperty("str")) {
                return_code(result.code, result.str);
            } else {
                message_box.innerText = "返回值错误";
                console.log("code", result.hasOwnProperty("code"));
                console.log("str", result.hasOwnProperty("str"));
                console.log(result)
            }
        } catch (e) {
            console.log("未知返回值", jsonText);
            console.log("错误代码", e);
        }
    }

}
