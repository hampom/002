import Stream from "mithril/stream";
import moment from "moment";

class Calendar {
    constructor() {
        this.today = Stream(moment());
        this.month = Stream.combine((today) => {
                return today().startOf("month");
            },
            [this.today]
        );
        this.calendarStartDate = Stream.combine((month) => {
                return month().clone().startOf("month").startOf("week");
            },
            [this.month]
        );
        this.calendarEndDate = Stream.combine((month) => {
                return month().clone().endOf("month").endOf("week");
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

                day[i].push(t.clone());
                t.add(1, "days");
            } while (t.isSameOrBefore(endDate()));

            return day;
        }, [this.calendarStartDate, this.calendarEndDate]);
    }
}

export default new Calendar();
