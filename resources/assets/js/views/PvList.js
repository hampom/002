import m from "mithril";
import moment from "moment";

import Event from "../models/Event"

export default class PvList {
    view(vnode) {
        return m(".mar-t-sm", [
            (Event.events().title)
                ? Object.keys(Event.events().title).map((id) => {
                    return m(".pl.mar-b-xs", [
                        m("span.switch.switch-info.switch-sm",
                            m("input[type=checkbox].switch",
                                {
                                    id: "event-" + id,
                                    onchange: (e) => { Event.events().title[id].visible = e.target.checked; },
                                    checked: Event.events().title[id].visible
                                }
                            ),
                            m("label", { "for": "event-" + id }, Event.events().title[id].name)
                        ),
                        m("a.text-sm", "編集")
                    ]);
                })
                : ""
        ])
    }
}
