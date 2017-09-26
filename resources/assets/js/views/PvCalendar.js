import m from "mithril";
import Stream from "mithril/stream";
import moment from "moment";

import Event from "../models/Event"

import PvList from "./PvList"

export default class PvCalendar {
    constructor(vnode) {
        this.today = Stream(moment());
        this.month = Stream.combine((today) => {
                return today().startOf("month");
            },
            [this.today]
        );
        this.calendarStartDate = Stream.combine((month) => {
                let t = moment(month().format("Y-MM-DD"));
                return moment(t.startOf("month").startOf("week").format("Y-MM-DD"));
            },
            [this.month]
        );
        this.calendarEndDate = Stream.combine((month) => {
                let t = moment(month().format("Y-MM-DD"));
                return moment(t.endOf("month").endOf("week").format("Y-MM-DD"));
            },
            [this.month]
        );
        this.day = Stream.combine((startDate, endDate) => {
            let day = [],
                t = startDate(),
                i = 0;
            do {
                if (t.day() === 0) {
                    day[++i] = [];
                }

                day[i].push(moment(t.format("Y-MM-DD")));
                t.add(1, "days");
            } while (t.isSameOrBefore(endDate()));

            return day;
        }, [this.calendarStartDate, this.calendarEndDate]);
    }

    prevMonth() {
        this.today(this.month().subtract(1, "months"));
    }

    nextMonth() {
        this.today(this.month().add(1, "months"));
    }

    view(vnode) {
        return [
            m("h2", vnode.state.today().format("Y/MM")),
            m(".row", [
                m(".col.text-left", m("span", { onclick: () => { vnode.state.prevMonth(); }}, "前月")),
                m(".col.text-right", m("span", { onclick: () => { vnode.state.nextMonth(); }}, "翌月")),
            ]),
            m(".calendar", {
                    style: {
                        "display": "table",
                        "width": "100%",
                    }
                },
                [
                    m(".header", {
                            style: {
                                "display": "table-row"
                            }
                        },
                        [
                            ["日", "月", "火", "水", "木", "金", "土"].map(function(week) {
                                return m("div",
                                    {
                                        style: {
                                            "display": "table-cell",
                                            "text-align": "center",
                                            "font-weight": "bold",
                                            "padding": "10px 0",
                                        }
                                    },
                                    week
                                );
                            })
                        ]),
                    vnode.state.day().map((week) => {
                        return m(".week", {
                                style: {
                                    "display": "table-row",
                                    "height": "100%"
                                }
                            },
                            [
                                week.map((day) => {
                                    let bd = day.month() !== vnode.state.today().month() ? "#f5f5f5" : "#ffffff";
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
                                                        "color": day.month() !== vnode.state.today().month() ? "#c5c5c5": "#000000"
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
                                                        return m("p.bg-primary.mar-b-xs", {
                                                                style: {
                                                                    "color": "#ffffff",
                                                                    "overflow": "hidden"
                                                                }
                                                            },
                                                            event.title
                                                        );
                                                    })
                                                : ""
                                        ])
                                })
                            ]);
                    })
                ]),

            // リスト
            m(PvList)
        ]
    }
}
