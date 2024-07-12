$(function () {
  let intervalId;

  const getTable = () =>
    window.location.pathname.split("/").pop().split(".").shift();
  const getSelectElement = () => $(`#${getTable()}-select`);
  const getDays = () => getSelectElement().val();
  const apiUrl = "../modules/utilities/delete_all.php";
  const updateUrl = "../modules/utilities/update_next_delete_all.php";

  const deleteTableData = (days, isImmediate = false) => {
    $.post(apiUrl, { table: getTable(), days, isImmediate })
      .done((data) => {
        if (isImmediate) window.location.reload();
      })
      .fail((xhr, status, error) => console.error(`Error: ${status} ${error}`));
  };

  const setDeleteInterval = (days) => {
    const milliseconds = days * 86400000; // 24 * 60 * 60 * 1000
    clearInterval(intervalId);
    intervalId = setInterval(() => deleteTableData(days), milliseconds);

    const nextRunDate = new Date(Date.now() + milliseconds).toISOString();
    localStorage.setItem("nextRun", nextRunDate);
    $.post(updateUrl, { nextRun: nextRunDate, tableName: getTable() });
  };

  const initialize = () => {
    const savedValue = localStorage.getItem(`${getTable()}Select`);
    if (savedValue) getSelectElement().val(savedValue);

    const nextRun = localStorage.getItem("nextRun");
    if (nextRun) {
      const nextRunDate = new Date(nextRun);
      const remainingTimeMs = nextRunDate - Date.now();
      setTimeout(() => {
        deleteTableData(getDays());
        setDeleteInterval(getDays());
      }, Math.max(0, remainingTimeMs));
    } else {
      setDeleteInterval(getDays());
    }
  };

  $(`#${getTable()}-delete`).click(() => {
    if (
      confirm(
        `ئایا دڵنیایت لە سڕینەوەی ئەو زانیارییانەی کە کۆنترن لە ${getDays()} ڕۆژ؟`
      )
    ) {
      deleteTableData(getDays(), true);
    }
  });

  getSelectElement().change(() => {
    const selectedValue = getDays();
    localStorage.setItem(`${getTable()}Select`, selectedValue);
    setDeleteInterval(selectedValue);
    window.location.reload();
  });

  initialize();
});
