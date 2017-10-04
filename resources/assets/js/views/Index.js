import m from "mithril";
import { Layout, LayoutContent, Grid, Cell } from 'mithrilmdl';
import Stream from "mithril/stream";

import PvCalendar from "./PvCalendar";
import PvList from "./PvList";
import Form from "./Form";

import Event from "../models/Event"
import User from "../models/User"

export default class Index {
  constructor(vnode) {
    this.calendar_id = Stream("");
  }

  oninit(vnode) {
    this.calendar_id(vnode.attrs.calendar_id);
    Event.load(this.calendar_id());
  }

  view(vnode) {
      return (
          <Layout>
              <LayoutContent>
              <Grid style={ "margin: 0" }>
                  <Cell col="3">
                      {User.hasToken() ? <Form /> : ""}
                      <PvList />
                  </Cell>
                  <Cell col="9"><PvCalendar /></Cell>
              </Grid>
              </LayoutContent>
          </Layout>
      );
  }
}
