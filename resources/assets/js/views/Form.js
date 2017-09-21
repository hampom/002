import m from "mithril";
import Stream from "mithril/stream";

export default class Form {
    constructor(vnode) {
    }

    view(vnode) {
        return m("form", [
            m(".input-field", [
                m("label", "日付"),
                m("input[type=text]")
            ]),
            m(".input-field", [
                m("label", "件名"),
                m("input[type=text]")
            ]),
            m("button", "登録")
        ])
    }
}