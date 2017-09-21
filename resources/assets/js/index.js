import m from "mithril";
import moment from "moment";
import Index from "./views/Index"

m.route.prefix("");
m.route(document.body, "/", {
  "/": Index,
});
