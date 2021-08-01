import 'view-design/dist/styles/iview.css';

import {
    Button ,
    Checkbox ,
    Switch ,
    Notice ,
    Drawer ,
    Modal ,
    Select ,
    Option ,
    RadioGroup ,
    Radio ,
    Table ,
    Message ,
    Icon ,
    BackTop ,
    Poptip ,
    Tooltip ,
    Tabs ,
    TabPane ,
    DatePicker ,
    Rate ,
    Menu ,
    MenuItem ,
    Submenu ,
    Dropdown ,
    DropdownMenu ,
    DropdownItem ,
} from 'view-design';



Vue.component('i-button' , Button);
Vue.component('i-checkbox' , Checkbox);
Vue.component('i-switch' , Switch);
Vue.component('i-drawer' , Drawer);
Vue.component('i-modal' , Modal);
Vue.component('i-radio-group' , RadioGroup);
Vue.component('i-radio' , Radio);
Vue.component('i-select' , Select);
Vue.component('i-option' , Option);
Vue.component('i-table' , Table);
Vue.component('i-icon' , Icon);
Vue.component('i-backtop' , BackTop);
Vue.component('i-poptip' , Poptip);
Vue.component('i-tab-pane' , TabPane);
Vue.component('i-tabs' , Tabs);
Vue.component('i-date-picker' , DatePicker);
Vue.component('i-tooltip' , Tooltip);
Vue.component('i-rate' , Rate);
Vue.component('i-menu' , Menu);
Vue.component('i-sub-menu' , Submenu);
Vue.component('i-menu-item' , MenuItem);
Vue.component('i-dropdown' , Dropdown);
Vue.component('i-dropdown-menu' , DropdownMenu);
Vue.component('i-dropdown-item' , DropdownItem);

Vue.prototype.$Notice = Notice;
Vue.prototype.$Modal = Modal;
Vue.prototype.$Message = Message;
