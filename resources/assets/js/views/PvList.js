import m from "mithril";
import { List, ListItem, ListItemPrimaryContent, ListItemSecondaryAction, Toggle } from 'mithrilmdl';

import Event from "../models/Event"

export default class PvList {
    edit(id) {
        Event.loadItem(id);
    }

    view(vnode) {
        return [
            <List>
                {(Event.events().title)
                    ? Object.keys(Event.events().title).map((id) => {
                        return [
                            <ListItem>
                                <ListItemPrimaryContent>
                                    <span
                                        onclick={(e) => this.edit(id)}
                                    >
                                        {Event.events().title[id].name}
                                    </span>
                                </ListItemPrimaryContent>
                                <ListItemSecondaryAction>
                                    <Toggle
                                        onclick={() => { Event.events().title[id].visible = !Event.events().title[id].visible; }}
                                        checked
                                    />
                                </ListItemSecondaryAction>
                            </ListItem>
                        ];
                    })
                    : ""
                }
            </List>
        ];
    }
}
