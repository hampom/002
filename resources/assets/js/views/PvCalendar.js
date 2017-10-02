import m from "mithril";
import moment from "moment";

import Event from "../models/Event"

import PvEvent from "../models/PvEvent"
import Calendar from "../models/Calendar"

export default class PvCalendar {
    static prevMonth() {
        Calendar.today(Calendar.month().subtract(1, "months"));
    }

    static nextMonth() {
        Calendar.today(Calendar.month().add(1, "months"));
    }

    view(vnode) {
        return [
            m(".row.mar-b-sm", [
                m(".col.text-left", m("button.button-light.button-sm", { onclick: () => { PvCalendar.prevMonth(); }}, "前月")),
                m(".col.text-center", Calendar.today().format("Y年MM月")),
                m(".col.text-right", m("button.button-light.button-sm", { onclick: () => { PvCalendar.nextMonth(); }}, "翌月")),
            ]),
            m(".calendar.mar-b-sm", {
                    style: {
                        "display": "table",
                        "width": "100%",
                        "height": "100%",
                    }
                },
                [
                    m(".header", {
                            style: {
                                "display": "table-row",
                                "height": "20px"
                            }
                        },
                        [
                            moment.weekdays().map(function(week) {
                                return m("div",
                                    {
                                        style: {
                                            "background": "#000",
                                            "color": "#fff",
                                            "display": "table-cell",
                                            "text-align": "center",
                                            "padding": "10px 0",
                                        }
                                    },
                                    week
                                );
                            })
                        ]),
                    Calendar.day().map((week) => {
                        return m(".week", {
                                style: {
                                    "display": "table-row",
                                }
                            },
                            [
                                week.map((day) => {
                                    let bd = day.month() !== Calendar.today().month() ? "#f5f5f5" : "#ffffff";
                                    return m("div.day", {
                                            style: {
                                                "background-color": bd,
                                                "display": "table-cell",
                                                "width": "14.285%",
                                                "border": "1px solid #c5c5c5",
                                                "padding": "5px"
                                            }
                                        },
                                        [
                                            m("p",
                                                {
                                                    style: {
                                                        "text-align": "right",
                                                        "font-weight": "bold",
                                                        "margin-bottom": "5px",
                                                        "color": day.month() !== Calendar.today().month() ? "#c5c5c5": "#000000"
                                                    }
                                                },
                                                day.format("DD")
                                            ),
                                            (Event.events().event && Event.events().event[day.format("Y-MM-DD")])
                                                ? Event.events().event[day.format("Y-MM-DD")]
                                                    .filter((event) => {
                                                        return Event.events().title[event.id].visible;
                                                    })
                                                    .map((event) => {
                                                        return m("p.mar-b-xs",
                                                            {
                                                                class: "bg-primary",
                                                                style: {
                                                                    "color": "#ffffff",
                                                                    "overflow": "hidden"
                                                                }
                                                            },
                                                            event.title
                                                        );
                                                    })
                                                : "",
                                            (PvEvent.calendar() && PvEvent.calendar()[day.format("Y-MM-DD")])
                                                ? m("p.bg-info.mar-b-xs",
                                                    {
                                                        style: {
                                                            "color": "#ffffff",
                                                            "overflow": "hidden"
                                                        }
                                                    },
                                                    PvEvent.calendar()[day.format("Y-MM-DD")].title
                                                )
                                                : ""
                                        ])
                                })
                            ]
                        );
                    })
                ]
            ),
            m("input[type=text].mar-b-sm", { "readonly": "readonly", "value": Event.icalUrl })
        ]
    }
}
