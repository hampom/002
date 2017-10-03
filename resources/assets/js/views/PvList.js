import m from "mithril";
import { List, ListItem, ListItemPrimaryContent, ListItemSecondaryAction, Toggle } from 'mithrilmdl';

import Event from "../models/Event"

export default class PvList {
    view(vnode) {
        return [
            <List>
                {(Event.events().title)
                    ? Object.keys(Event.events().title).map((id) => {
                        return [
                            <ListItem>
                                <ListItemPrimaryContent>
                                    {Event.events().title[id].name}
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
