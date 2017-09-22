import m from "mithril";
import Stream from "mithril/stream";

import Event from "../models/Event";

export default class Form {
    constructor(vnode) {
        this.date = Stream("");
        this.title = Stream("");
    }

    add() {
        Event
           .add(this.date, this.title)
           .then(() => {
               this.date("");
               this.title("");
           })
    }

    view(vnode) {
        return m("form",
            {
                onsubmit: (e) => {
                    e.preventDefault();
                    vnode.state.add();
                }
            },
            [
                m(".input-field", [
                    m("label", "日付"),
                    m("input[type=text]", { oninput: m.withAttr("value", vnode.state.date), value: vnode.state.date })
                ]),
                m(".input-field", [
                    m("label", "件名"),
                    m("input[type=text]", { oninput: m.withAttr("value", vnode.state.title), value: vnode.state.title })
                ]),
                m("button", "登録")
            ])
    }
}