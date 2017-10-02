import m from "mithril";
import moment from "moment";
import { Grid, Cell, Button } from 'mithrilmdl';

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
            <div>
                <Grid>
                    <Cell>
                        <div style={ "text-align: left;" }>
                            <Button
                                onclick={(e) => PvCalendar.prevMonth()}
                                title="前月"
                            />
                        </div>
                    </Cell>
                    <Cell>
                        <div style={ "text-align: center;" }>
                            {Calendar.today().format("Y年MM月")}
                        </div>
                    </Cell>
                    <Cell>
                        <div style={ "text-align: right;" }>
                            <Button
                                onclick={(e) => PvCalendar.nextMonth()}
                                title="翌月"
                            />
                        </div>
                    </Cell>
                </Grid>
                <div style={ "display: table; width: 100%; height: 100%;" }>
                    <div style={ "display: table-row; height: 20px;" }>
                        {moment.weekdays().map((week) => {
                            return [
                                <div style={ "display: table-cell; text-align: center; padding: 10px 0;" }>
                                    {week}
                                </div>
                            ];
                        })}
                    </div>
                    {Calendar.day().map((week) => {
                        return [
                            <div style={ "display: table-row" }>
                                {week.map((day) => {
                                    let bd = day.month() !== Calendar.today().month() ? "#f5f5f5" : "#ffffff";
                                    return [
                                        <div style={ "display: table-cell; width: 14.285%; border: 1px solid #c5c5c5; padding: 5px; background-color:" + bd }>
                                            <p style={ "text-align: right; font-weight: bold;" }>{day.format("DD")}</p>
                                            {(Event.events().event && Event.events().event[day.format("Y-MM-DD")])
                                                ? Event.events().event[day.format("Y-MM-DD")]
                                                    .filter((event) => { return Event.events().title[event.id].visible; })
                                                    .map((event) => {
                                                        return [
                                                            <p style={ "margin: 0; overflow: hidden;" }>{event.title}</p>
                                                        ]
                                                    })
                                                : ""
                                            }
                                            {(PvEvent.calendar() && PvEvent.calendar()[day.format("Y-MM-DD")])
                                                ?
                                                <p style={ "margin: 0; overflow: hidden;" }>
                                                    {PvEvent.calendar()[day.format("Y-MM-DD")].title}
                                                </p>
                                                : ""
                                            }
                                        </div>
                                    ]
                                })}
                            </div>
                        ]
                    })}
                </div>
            </div>
        ];
    }
}
