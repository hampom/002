import m from "mithril";
import Stream from "mithril/stream";

import Event from "../models/Event";
import PvEvent from "../models/PvEvent";

export default class Form {
    constructor(vnode) {
        this.date = Stream("");
        this.title = Stream("");
        this.interval_setting = Stream("N");
        this.interval_num = Stream("");

        this.intervals = {
            "N": "なし",
            "Y": "年",
            "M": "月",
            "W": "週",
            "D": "日",
        };
    }

    add() {
        Event
           .add(this.date, this.title, this.interval_setting, this.interval_num)
           .then(() => {
               this.date("");
               this.title("");
               this.interval_setting("N");
               this.interval_num("");
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
                    m("input[type=text]", {
                        oninput: m.withAttr("value", PvEvent.startAt),
                        value: PvEvent.startAt
                    })
                ]),
                m(".input-field", [
                    m("label", "件名"),
                    m("input[type=text]", {
                        oninput: m.withAttr("value", PvEvent.title),
                        value: PvEvent.title
                    })
                ]),
                m("fieldset", [
                    m("legend", "繰り返し設定"),
                    m(".input-field", [
                        Object.keys(vnode.state.intervals).map((key) => {
                            return m("label", [
                                m("input[type=radio]", {
                                    value: key,
                                    onchange: m.withAttr("value", PvEvent.interval_setting),
                                    checked: PvEvent.interval_setting() == key
                                }),
                                this.intervals[key]
                            ]);
                        })
                    ]),
                    m(".input-field", [
                        m("label", "繰り返し周期"),
                        m(".input-group", [
                            m("input[type=text]", {
                                oninput: m.withAttr("value", PvEvent.interval_num),
                                value: PvEvent.interval_num,
                                disabled: PvEvent.interval_setting() === "N"
                            }),
                            m("span.input-addon", "/" + vnode.state.intervals[PvEvent.interval_setting()])
                        ])
                    ]),
                ]),
                m("button", "登録")
            ])
    }
}