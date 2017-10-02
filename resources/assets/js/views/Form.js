import m from "mithril";
import Stream from "mithril/stream";
import { Button, TextField, TextFieldLabel, TextFieldInput } from 'mithrilmdl';

import Event from "../models/Event";
import PvEvent from "../models/PvEvent";

export default class FormView {
    constructor(vnode) {
        this.intervals = {
            "N": "なし",
            "Y": "年",
            "M": "月",
            "W": "週",
            "D": "日",
        };
    }

    add(e) {
        e.preventDefault();
        Event
           .add(PvEvent.startAt, PvEvent.title, PvEvent.interval_setting, PvEvent.interval_num)
           .then(() => {
               PvEvent.startAt("");
               PvEvent.title("");
               PvEvent.interval_setting("N");
               PvEvent.interval_num("");
               document.querySelector("[for=interval-N]").MaterialRadio.check();
           });
    }

    view(vnode) {
        return (
            <form
                onSubmit={e => vnode.state.add(e)}
            >
                <TextField floatingLabel>
                    <TextFieldLabel value="日付" />
                    <TextFieldInput
                        pattern="[0-9]{4}-[0-9]{2}-[0-9]{2}"
                        value={PvEvent.startAt}
                        oninput={m.withAttr("value", PvEvent.startAt)}
                    />
                </TextField>

                <TextField floatingLabel>
                    <TextFieldLabel value="用件" />
                    <TextFieldInput
                        value={PvEvent.title}
                        oninput={m.withAttr("value", PvEvent.title)}
                    />
                </TextField>

                <fieldset>
                    <legend style={ "margin-bottom: 10px;" }>繰り返し設定</legend>
                    {Object.keys(vnode.state.intervals).map((key) => {
                        return (
                            <div style={"margin-bottom: 6px;"}>
                                <label class="mdl-radio mdl-js-radio" for={"interval-" + key}>
                                    <input
                                        type="radio"
                                        id={"interval-" + key}
                                        class="mdl-radio__button"
                                        value={key}
                                        name="interval_setting"
                                        onchange={m.withAttr("value", PvEvent.interval_setting)}
                                        checked={PvEvent.interval_setting() === key}
                                    />
                                    <span class="mdl-radio__label">{this.intervals[key]}</span>
                                </label>
                            </div>
                        );
                    })}
                </fieldset>

                {(PvEvent.interval_setting() !== "N")
                    ? <TextField floatingLabel>
                        <TextFieldLabel value="周期"/>
                        <TextFieldInput
                            value={PvEvent.interval_num}
                            oninput={m.withAttr("value", PvEvent.interval_num)}
                        />
                    </TextField>
                    : ""
                }
                <Button raised colored title="登録" onclick={e => vnode.state.add(e)} />
            </form>
        );
    }
}