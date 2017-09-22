import m from "mithril";
import Stream from "mithril/stream";

import PvCalendar from "./PvCalendar";
import Form from "./Form";

import Event from "../models/Event"

export default class Index {
  constructor(vnode) {
    this.calendar_id = Stream("");
  }

  oninit(vnode) {
    this.calendar_id(vnode.attrs.calendar_id);
    Event.load(this.calendar_id());
  }

  view(vnode) {
    return m(".container.mar-t-md", [
      m(".row", [
        m(".col-4", m(Form)),
        m(".col-8", m(PvCalendar))
      ])
    ]);
  }
}
