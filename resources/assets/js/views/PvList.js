import m from "mithril";
import moment from "moment";

import Event from "../models/Event"

export default class PvList {
    view(vnode) {
        return m("ul", [
            (Event.events().title)
                ? Object.keys(Event.events().title).map((id) => {
                    return m("li", {
                        onclick: (e) => {
                            Event.events().title[id].visible = !Event.events().title[id].visible;
                        }
                    }, Event.events().title[id].name);
                })
                : ""
        ])
    }
}
