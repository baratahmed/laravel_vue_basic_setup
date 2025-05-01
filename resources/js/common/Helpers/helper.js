import moment from "moment";

export function dateFormat(value) {
  if (value) {
    return moment(String(value)).format("LLL");
  }
}

export function onlyDateFormat(value) {
  if (value) {
    return moment(String(value)).format("ll");
  }
}