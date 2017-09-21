import m from "mithril";
import Stream from "mithril/stream";
import moment from "moment";

import Event from "../models/Event"

export default class PvCalendar {
    constructor(vnode) {
        this.today = moment();
        this.month = this.today.startOf("month");
        this.startWeekNumber = this.month.week();
        this.endWeekNumber = this.today.endOf("month").week();
        this.calendarStartDate = moment(this.today.year() + "-W" + (this.startWeekNumber - 1) + "-7");
        this.calendarEndDate = moment(this.today.year() + "-W" + this.endWeekNumber + "-7");
        this.day = [];

        this.generater();
    }

    generater() {
        var tmp = [];
        while (this.calendarStartDate.isBefore(this.calendarEndDate)) {
            tmp.push(moment(this.calendarStartDate));
            if (this.calendarStartDate.day() === 6) {
                this.day.push(tmp);
                tmp = [];
            }

            this.calendarStartDate.add(1, "day");
        }
    }

    view(vnode) {
        return [
            m("h2", vnode.state.today.format("Y/MM")),
            m("table.table-bordered", [
                m("thead", [
                    m("th", "日"),
                    m("th", "月"),
                    m("th", "火"),
                    m("th", "水"),
                    m("th", "木"),
                    m("th", "金"),
                    m("th", "土"),
                ]),
                m("tbody",
                    vnode.state.day.map((week, index) => {
                        return m("tr", [
                            week.map((day) => {
                                let bd = day.month() !== vnode.state.today.month() ? "#f5f5f5" : "#ffffff";
                                return m("td",
                                    {
                                        style: {
                                            "background": bd,
                                            "vertical-align": "top",
                                            "height": "5em"
                                        }
                                    },
                                    [
                                        day.format("DD"),
                                        (Event.events()[day.format("Y-MM-DD")])
                                            ? Event.events()[day.format("Y-MM-DD")].map((event) => {
                                                return m("p", event.title);
                                            })
                                            : ""
                                    ]
                                );
                            })
                        ]);
                    })
                )
            ])
        ]
    }
}
