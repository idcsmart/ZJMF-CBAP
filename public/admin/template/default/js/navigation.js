(function (window, undefined) {
    var old_onload = window.onload
    window.onload = function () {
        const template = document.getElementsByClassName('navigation')[0]
        Vue.prototype.lang = window.lang
        Vue.prototype.moment = moment

        new Vue({
            data() {
                return {
                    maxHeight: 0,
                    // 菜单列表
                    menuList: [],
                    // 系统导航列表
                    systemList: [],
                    // 插件导航列表
                    pluginList: [],
                    // 模块导航列表
                    moduleList: [],
                    // 语言
                    language: [],
                    // 导航类型
                    menuType: [
                        { id: 1, label: "系统页面", value: "system" },
                        { id: 2, label: "插件", value: "plugin" },
                        { id: 3, label: "自定义", value: "custom" },
                        { id: 4, label: "模块", value: "module" }
                    ],
                    // 正在拖拽的导航数据
                    draggleItem: 0,
                    // 正在拖动的导航的id
                    moveId: 0,
                    // 目标节点是否为1级导航
                    isLv1: true,
                    // 目标页面选择列表
                    selectList: [],
                    // 前台导航loading
                    homeMenuLoading: false,
                    // 后台导航 loading
                    adminMenuLoading: false,
                    // 右侧设置框
                    isShowSet: false,
                    // 激活的导航id
                    activeId: 0,
                    // 鼠标点下时的坐标
                    startXy: {
                        x: 0,
                        y: 0
                    },
                    // 鼠标移动中的坐标
                    endXy: {
                        x: 0,
                        y: 0
                    },
                    // 导航 前后导航选择 1前台 2后台
                    value: '1',
                    // 导航右侧设置表单数据
                    formData: {
                        type: "",
                        url: "",
                        icon: "",
                        name: "",
                        language: {
                        }
                    },
                    // 设置表单
                    // setRules: {
                    //     type: [
                    //         { required: true, message: "页面类型不能为空", type: 'error' },
                    //     ],
                    //     name: [{ required: true, message: "导航名称不能为空", type: 'error' },],
                    // },
                    // 新增页面弹窗相关
                    // 新增导航页面设置表单数据
                    newFormData: {
                        id: "",
                        type: "",
                        url: "",
                        icon: "",
                        name: "",
                        nav_id: "",
                        isChecked: false,
                        language: {
                        }
                    },
                    // newRules: {
                    //     type: [
                    //         { required: true, message: "页面类型不能为空", type: 'error' },
                    //     ],
                    //     name: [{ required: true, message: "导航名称不能为空", type: 'error' },],
                    // },
                    // 是否显示新增页面弹窗
                    visible: false,
                    commonLang: JSON.parse(localStorage.getItem('common_set')).lang_home[0].display_lang,
                    iconsData: [],
                    popupVisible: false,
                    backPopupVisible: false,
                    newPopupVisible: false,
                    newBackPopupVisible: false,
                    manifest: [{
                        stem: "add-circle",
                        icon: "AddCircle"
                    }, {
                        stem: "add-rectangle",
                        icon: "AddRectangle"
                    }, {
                        stem: "add",
                        icon: "Add"
                    }, {
                        stem: "app",
                        icon: "App"
                    }, {
                        stem: "arrow-down-rectangle",
                        icon: "ArrowDownRectangle"
                    }, {
                        stem: "arrow-down",
                        icon: "ArrowDown"
                    }, {
                        stem: "arrow-left",
                        icon: "ArrowLeft"
                    }, {
                        stem: "arrow-right",
                        icon: "ArrowRight"
                    }, {
                        stem: "arrow-up",
                        icon: "ArrowUp"
                    }, {
                        stem: "attach",
                        icon: "Attach"
                    }, {
                        stem: "backtop-rectangle",
                        icon: "BacktopRectangle"
                    }, {
                        stem: "backtop",
                        icon: "Backtop"
                    }, {
                        stem: "backward",
                        icon: "Backward"
                    }, {
                        stem: "barcode",
                        icon: "Barcode"
                    }, {
                        stem: "books",
                        icon: "Books"
                    }, {
                        stem: "browse-off",
                        icon: "BrowseOff"
                    }, {
                        stem: "browse",
                        icon: "Browse"
                    }, {
                        stem: "bulletpoint",
                        icon: "Bulletpoint"
                    }, {
                        stem: "calendar",
                        icon: "Calendar"
                    }, {
                        stem: "call",
                        icon: "Call"
                    }, {
                        stem: "caret-down-small",
                        icon: "CaretDownSmall"
                    }, {
                        stem: "caret-down",
                        icon: "CaretDown"
                    }, {
                        stem: "caret-left-small",
                        icon: "CaretLeftSmall"
                    }, {
                        stem: "caret-left",
                        icon: "CaretLeft"
                    }, {
                        stem: "caret-right-small",
                        icon: "CaretRightSmall"
                    }, {
                        stem: "caret-right",
                        icon: "CaretRight"
                    }, {
                        stem: "caret-up-small",
                        icon: "CaretUpSmall"
                    }, {
                        stem: "caret-up",
                        icon: "CaretUp"
                    }, {
                        stem: "cart",
                        icon: "Cart"
                    }, {
                        stem: "chart-bar",
                        icon: "ChartBar"
                    }, {
                        stem: "chart-bubble",
                        icon: "ChartBubble"
                    }, {
                        stem: "chart-pie",
                        icon: "ChartPie"
                    }, {
                        stem: "chart",
                        icon: "Chart"
                    }, {
                        stem: "chat",
                        icon: "Chat"
                    }, {
                        stem: "check-circle-filled",
                        icon: "CheckCircleFilled"
                    }, {
                        stem: "check-circle",
                        icon: "CheckCircle"
                    }, {
                        stem: "check-rectangle-filled",
                        icon: "CheckRectangleFilled"
                    }, {
                        stem: "check-rectangle",
                        icon: "CheckRectangle"
                    }, {
                        stem: "check",
                        icon: "Check"
                    }, {
                        stem: "chevron-down-circle",
                        icon: "ChevronDownCircle"
                    }, {
                        stem: "chevron-down-rectangle",
                        icon: "ChevronDownRectangle"
                    }, {
                        stem: "chevron-down",
                        icon: "ChevronDown"
                    }, {
                        stem: "chevron-left-circle",
                        icon: "ChevronLeftCircle"
                    }, {
                        stem: "chevron-left-double",
                        icon: "ChevronLeftDouble"
                    }, {
                        stem: "chevron-left-rectangle",
                        icon: "ChevronLeftRectangle"
                    }, {
                        stem: "chevron-left",
                        icon: "ChevronLeft"
                    }, {
                        stem: "chevron-right-circle",
                        icon: "ChevronRightCircle"
                    }, {
                        stem: "chevron-right-double",
                        icon: "ChevronRightDouble"
                    }, {
                        stem: "chevron-right-rectangle",
                        icon: "ChevronRightRectangle"
                    }, {
                        stem: "chevron-right",
                        icon: "ChevronRight"
                    }, {
                        stem: "chevron-up-circle",
                        icon: "ChevronUpCircle"
                    }, {
                        stem: "chevron-up-rectangle",
                        icon: "ChevronUpRectangle"
                    }, {
                        stem: "chevron-up",
                        icon: "ChevronUp"
                    }, {
                        stem: "circle",
                        icon: "Circle"
                    }, {
                        stem: "clear",
                        icon: "Clear"
                    }, {
                        stem: "close-circle-filled",
                        icon: "CloseCircleFilled"
                    }, {
                        stem: "close-circle",
                        icon: "CloseCircle"
                    }, {
                        stem: "close-rectangle",
                        icon: "CloseRectangle"
                    }, {
                        stem: "close",
                        icon: "Close"
                    }, {
                        stem: "cloud-download",
                        icon: "CloudDownload"
                    }, {
                        stem: "cloud-upload",
                        icon: "CloudUpload"
                    }, {
                        stem: "cloud",
                        icon: "Cloud"
                    }, {
                        stem: "code",
                        icon: "Code"
                    }, {
                        stem: "control-platform",
                        icon: "ControlPlatform"
                    }, {
                        stem: "creditcard",
                        icon: "Creditcard"
                    }, {
                        stem: "dashboard",
                        icon: "Dashboard"
                    }, {
                        stem: "delete",
                        icon: "Delete"
                    }, {
                        stem: "desktop",
                        icon: "Desktop"
                    }, {
                        stem: "discount-filled",
                        icon: "DiscountFilled"
                    }, {
                        stem: "discount",
                        icon: "Discount"
                    }, {
                        stem: "download",
                        icon: "Download"
                    }, {
                        stem: "edit-1",
                        icon: "Edit1"
                    }, {
                        stem: "edit",
                        icon: "Edit"
                    }, {
                        stem: "ellipsis",
                        icon: "Ellipsis"
                    }, {
                        stem: "enter",
                        icon: "Enter"
                    }, {
                        stem: "error-circle-filled",
                        icon: "ErrorCircleFilled"
                    }, {
                        stem: "error-circle",
                        icon: "ErrorCircle"
                    }, {
                        stem: "error",
                        icon: "Error"
                    }, {
                        stem: "file-add",
                        icon: "FileAdd"
                    }, {
                        stem: "file-copy",
                        icon: "FileCopy"
                    }, {
                        stem: "file-excel",
                        icon: "FileExcel"
                    }, {
                        stem: "file-image",
                        icon: "FileImage"
                    }, {
                        stem: "file-paste",
                        icon: "FilePaste"
                    }, {
                        stem: "file-pdf",
                        icon: "FilePdf"
                    }, {
                        stem: "file-powerpoint",
                        icon: "FilePowerpoint"
                    }, {
                        stem: "file-unknown",
                        icon: "FileUnknown"
                    }, {
                        stem: "file-word",
                        icon: "FileWord"
                    }, {
                        stem: "file",
                        icon: "File"
                    }, {
                        stem: "filter-clear",
                        icon: "FilterClear"
                    }, {
                        stem: "filter",
                        icon: "Filter"
                    }, {
                        stem: "flag",
                        icon: "Flag"
                    }, {
                        stem: "folder-add",
                        icon: "FolderAdd"
                    }, {
                        stem: "folder-open",
                        icon: "FolderOpen"
                    }, {
                        stem: "folder",
                        icon: "Folder"
                    }, {
                        stem: "fork",
                        icon: "Fork"
                    }, {
                        stem: "format-horizontal-align-bottom",
                        icon: "FormatHorizontalAlignBottom"
                    }, {
                        stem: "format-horizontal-align-center",
                        icon: "FormatHorizontalAlignCenter"
                    }, {
                        stem: "format-horizontal-align-top",
                        icon: "FormatHorizontalAlignTop"
                    }, {
                        stem: "format-vertical-align-center",
                        icon: "FormatVerticalAlignCenter"
                    }, {
                        stem: "format-vertical-align-left",
                        icon: "FormatVerticalAlignLeft"
                    }, {
                        stem: "format-vertical-align-right",
                        icon: "FormatVerticalAlignRight"
                    }, {
                        stem: "forward",
                        icon: "Forward"
                    }, {
                        stem: "fullscreen-exit",
                        icon: "FullscreenExit"
                    }, {
                        stem: "fullscreen",
                        icon: "Fullscreen"
                    }, {
                        stem: "gender-female",
                        icon: "GenderFemale"
                    }, {
                        stem: "gender-male",
                        icon: "GenderMale"
                    }, {
                        stem: "gift",
                        icon: "Gift"
                    }, {
                        stem: "heart-filled",
                        icon: "HeartFilled"
                    }, {
                        stem: "heart",
                        icon: "Heart"
                    }, {
                        stem: "help-circle-filled",
                        icon: "HelpCircleFilled"
                    }, {
                        stem: "help-circle",
                        icon: "HelpCircle"
                    }, {
                        stem: "help",
                        icon: "Help"
                    }, {
                        stem: "history",
                        icon: "History"
                    }, {
                        stem: "home",
                        icon: "Home"
                    }, {
                        stem: "hourglass",
                        icon: "Hourglass"
                    }, {
                        stem: "image",
                        icon: "Image"
                    }, {
                        stem: "info-circle-filled",
                        icon: "InfoCircleFilled"
                    }, {
                        stem: "info-circle",
                        icon: "InfoCircle"
                    }, {
                        stem: "internet",
                        icon: "Internet"
                    }, {
                        stem: "jump",
                        icon: "Jump"
                    }, {
                        stem: "laptop",
                        icon: "Laptop"
                    }, {
                        stem: "layers",
                        icon: "Layers"
                    }, {
                        stem: "link-unlink",
                        icon: "LinkUnlink"
                    }, {
                        stem: "link",
                        icon: "Link"
                    }, {
                        stem: "loading",
                        icon: "Loading"
                    }, {
                        stem: "location",
                        icon: "Location"
                    }, {
                        stem: "lock-off",
                        icon: "LockOff"
                    }, {
                        stem: "lock-on",
                        icon: "LockOn"
                    }, {
                        stem: "login",
                        icon: "Login"
                    }, {
                        stem: "logo-android",
                        icon: "LogoAndroid"
                    }, {
                        stem: "logo-apple-filled",
                        icon: "LogoAppleFilled"
                    }, {
                        stem: "logo-apple",
                        icon: "LogoApple"
                    }, {
                        stem: "logo-chrome-filled",
                        icon: "LogoChromeFilled"
                    }, {
                        stem: "logo-chrome",
                        icon: "LogoChrome"
                    }, {
                        stem: "logo-codepen",
                        icon: "LogoCodepen"
                    }, {
                        stem: "logo-github-filled",
                        icon: "LogoGithubFilled"
                    }, {
                        stem: "logo-github",
                        icon: "LogoGithub"
                    }, {
                        stem: "logo-ie-filled",
                        icon: "LogoIeFilled"
                    }, {
                        stem: "logo-ie",
                        icon: "LogoIe"
                    }, {
                        stem: "logo-windows-filled",
                        icon: "LogoWindowsFilled"
                    }, {
                        stem: "logo-windows",
                        icon: "LogoWindows"
                    }, {
                        stem: "logout",
                        icon: "Logout"
                    }, {
                        stem: "mail",
                        icon: "Mail"
                    }, {
                        stem: "menu-fold",
                        icon: "MenuFold"
                    }, {
                        stem: "menu-unfold",
                        icon: "MenuUnfold"
                    }, {
                        stem: "minus-circle-filled",
                        icon: "MinusCircleFilled"
                    }, {
                        stem: "minus-circle",
                        icon: "MinusCircle"
                    }, {
                        stem: "minus-rectangle",
                        icon: "MinusRectangle"
                    }, {
                        stem: "mobile-vibrate",
                        icon: "MobileVibrate"
                    }, {
                        stem: "mobile",
                        icon: "Mobile"
                    }, {
                        stem: "money-circle",
                        icon: "MoneyCircle"
                    }, {
                        stem: "more",
                        icon: "More"
                    }, {
                        stem: "move",
                        icon: "Move"
                    }, {
                        stem: "next",
                        icon: "Next"
                    }, {
                        stem: "notification-filled",
                        icon: "NotificationFilled"
                    }, {
                        stem: "notification",
                        icon: "Notification"
                    }, {
                        stem: "order-adjustment-column",
                        icon: "OrderAdjustmentColumn"
                    }, {
                        stem: "order-ascending",
                        icon: "OrderAscending"
                    }, {
                        stem: "order-descending",
                        icon: "OrderDescending"
                    }, {
                        stem: "page-first",
                        icon: "PageFirst"
                    }, {
                        stem: "page-last",
                        icon: "PageLast"
                    }, {
                        stem: "pause-circle-filled",
                        icon: "PauseCircleFilled"
                    }, {
                        stem: "photo",
                        icon: "Photo"
                    }, {
                        stem: "pin",
                        icon: "Pin"
                    }, {
                        stem: "play-circle-filled",
                        icon: "PlayCircleFilled"
                    }, {
                        stem: "play-circle-stroke",
                        icon: "PlayCircleStroke"
                    }, {
                        stem: "play-circle",
                        icon: "PlayCircle"
                    }, {
                        stem: "play",
                        icon: "Play"
                    }, {
                        stem: "poweroff",
                        icon: "Poweroff"
                    }, {
                        stem: "precise-monitor",
                        icon: "PreciseMonitor"
                    }, {
                        stem: "previous",
                        icon: "Previous"
                    }, {
                        stem: "print",
                        icon: "Print"
                    }, {
                        stem: "qrcode",
                        icon: "Qrcode"
                    }, {
                        stem: "queue",
                        icon: "Queue"
                    }, {
                        stem: "rectangle",
                        icon: "Rectangle"
                    }, {
                        stem: "refresh",
                        icon: "Refresh"
                    }, {
                        stem: "remove",
                        icon: "Remove"
                    }, {
                        stem: "rollback",
                        icon: "Rollback"
                    }, {
                        stem: "root-list",
                        icon: "RootList"
                    }, {
                        stem: "round",
                        icon: "Round"
                    }, {
                        stem: "save",
                        icon: "Save"
                    }, {
                        stem: "scan",
                        icon: "Scan"
                    }, {
                        stem: "search",
                        icon: "Search"
                    }, {
                        stem: "secured",
                        icon: "Secured"
                    }, {
                        stem: "server",
                        icon: "Server"
                    }, {
                        stem: "service",
                        icon: "Service"
                    }, {
                        stem: "setting",
                        icon: "Setting"
                    }, {
                        stem: "share",
                        icon: "Share"
                    }, {
                        stem: "shop",
                        icon: "Shop"
                    }, {
                        stem: "slash",
                        icon: "Slash"
                    }, {
                        stem: "sound",
                        icon: "Sound"
                    }, {
                        stem: "star-filled",
                        icon: "StarFilled"
                    }, {
                        stem: "star",
                        icon: "Star"
                    }, {
                        stem: "stop-circle-1",
                        icon: "StopCircle1"
                    }, {
                        stem: "stop-circle-filled",
                        icon: "StopCircleFilled"
                    }, {
                        stem: "stop-circle",
                        icon: "StopCircle"
                    }, {
                        stem: "stop",
                        icon: "Stop"
                    }, {
                        stem: "swap-left",
                        icon: "SwapLeft"
                    }, {
                        stem: "swap-right",
                        icon: "SwapRight"
                    }, {
                        stem: "swap",
                        icon: "Swap"
                    }, {
                        stem: "thumb-down",
                        icon: "ThumbDown"
                    }, {
                        stem: "thumb-up",
                        icon: "ThumbUp"
                    }, {
                        stem: "time-filled",
                        icon: "TimeFilled"
                    }, {
                        stem: "time",
                        icon: "Time"
                    }, {
                        stem: "tips",
                        icon: "Tips"
                    }, {
                        stem: "tools",
                        icon: "Tools"
                    }, {
                        stem: "unfold-less",
                        icon: "UnfoldLess"
                    }, {
                        stem: "unfold-more",
                        icon: "UnfoldMore"
                    }, {
                        stem: "upload",
                        icon: "Upload"
                    }, {
                        stem: "usb",
                        icon: "Usb"
                    }, {
                        stem: "user-add",
                        icon: "UserAdd"
                    }, {
                        stem: "user-avatar",
                        icon: "UserAvatar"
                    }, {
                        stem: "user-circle",
                        icon: "UserCircle"
                    }, {
                        stem: "user-clear",
                        icon: "UserClear"
                    }, {
                        stem: "user-talk",
                        icon: "UserTalk"
                    }, {
                        stem: "user",
                        icon: "User"
                    }, {
                        stem: "usergroup-add",
                        icon: "UsergroupAdd"
                    }, {
                        stem: "usergroup-clear",
                        icon: "UsergroupClear"
                    }, {
                        stem: "usergroup",
                        icon: "Usergroup"
                    }, {
                        stem: "video",
                        icon: "Video"
                    }, {
                        stem: "view-column",
                        icon: "ViewColumn"
                    }, {
                        stem: "view-list",
                        icon: "ViewList"
                    }, {
                        stem: "view-module",
                        icon: "ViewModule"
                    }, {
                        stem: "wallet",
                        icon: "Wallet"
                    }, {
                        stem: "wifi",
                        icon: "Wifi"
                    }, {
                        stem: "zoom-in",
                        icon: "ZoomIn"
                    }, {
                        stem: "zoom-out",
                        icon: "ZoomOut"
                    }],
                    productList: [
                    ],
                    newProductList: [],
                    newModuleSelectLoading: false,
                    treeProps: {
                        keys: {
                            label: 'name',
                            value: 'id',
                            children: 'child',
                        },
                    },
                    treeKey: {
                        label: 'name',
                        value: 'id',
                        children: 'child',
                    }
                }
            },
            components: {
                vuedraggable
            },
            mounted() {
                this.maxHeight = document.getElementById('content').clientHeight - 170
                let timer = null
                window.onresize = () => {
                    if (timer) {
                        return
                    }
                    timer = setTimeout(() => {
                        this.maxHeight = document.getElementById('content').clientHeight - 170
                        clearTimeout(timer)
                        timer = null
                    }, 300)
                }
            },
            methods: {
                // 获取前台导航
                getHomeMenu() {
                    this.homeMenuLoading = true
                    homeMenu().then(res => {
                        if (res.data.status === 200) {
                            const menu = res.data.data.menu
                            menu.map(item => {
                                if (item.type == 'system(command)') {
                                    item.type = 'system'
                                }

                                if (!item.child) {
                                    // 若没有child 则将子导航拖进去会失效
                                    item.child = []
                                } else {
                                    item.child.map(n => {
                                        if (!n.child) {
                                            n.child = []
                                        }
                                        if (n.type == 'system(command)') {
                                            n.type = 'system'
                                        }
                                    })
                                }
                            })
                            // 前台导航
                            this.menuList = menu
                            // 系统默认导航
                            this.systemList = res.data.data.system_nav
                            // 插件默认导航
                            this.pluginList = res.data.data.plugin_nav
                            // 模块默认导航
                            this.moduleList = res.data.data.module
                            // 语言列表
                            this.language = res.data.data.language
                            this.language.forEach(item => {
                                this.formData[item.display_lang] = ""
                                this.newFormData[item.display_lang] = ""
                            });
                            this.homeMenuLoading = false
                        }
                    }).catch(error => {
                        this.$message.error(error.data.msg)
                        this.homeMenuLoading = false
                    })
                },
                // 获取后台导航
                getAdminMenu() {
                    this.adminMenuLoading = true
                    adminMenu().then(res => {
                        if (res.data.status === 200) {
                            const menu = res.data.data.menu

                            menu.map(item => {
                                if (item.type == 'system(command)') {
                                    item.type = 'system'
                                }

                                if (!item.child) {
                                    // 若没有child 则将子导航拖进去会失效
                                    item.child = []
                                } else {
                                    // 若二级导航没有 child 则其变成一级导航时不能为其添加子导航
                                    item.child.map(n => {
                                        if (!n.child) {
                                            n.child = []
                                        }
                                        if (n.type == 'system(command)') {
                                            n.type = 'system'
                                        }
                                    })
                                }
                            })
                            // 后台导航
                            this.menuList = menu
                            // 系统默认导航
                            const systemList = res.data.data.system_nav
                            let num = 0
                            systemList.map(item => {
                                if (!item.url) {
                                    item.url = 'menu' + num
                                    num += 1
                                }
                            })
                            this.systemList = systemList
                            // 插件默认导航
                            const pluginList = res.data.data.plugin_nav
                            pluginList.map(item => {
                                item.navs.map(n => {
                                    if (!n.url) {
                                        n.url = 'menu' + num
                                        num += 1
                                    }
                                })
                            })
                            this.pluginList = pluginList
                            // 语言列表
                            this.language = res.data.data.language
                            this.language.forEach(item => {
                                this.formData[item.display_lang] = ""
                                this.newFormData[item.display_lang] = ""
                            });
                            this.adminMenuLoading = false
                        }
                    }).catch(error => {
                        this.$message.error(error.data.msg)
                        this.adminMenuLoading = false
                    })
                },
                // 前后台导航切换
                menuChange(value) {
                    this.popupVisible = false
                    this.backPopupVisible = false
                    this.newPopupVisible = false
                    this.newBackPopupVisible = false,
                        // 隐藏右侧设置页面
                        this.isShowSet = false
                    this.menuList = []
                    if (this.value == 1) {
                        // 获取前台导航
                        this.getHomeMenu()
                        this.menuType = [
                            { id: 1, label: "系统页面", value: "system" },
                            { id: 2, label: "插件", value: "plugin" },
                            { id: 3, label: "自定义", value: "custom" },
                            { id: 4, label: "模块", value: "module" }
                        ]
                    }
                    if (this.value == 2) {
                        // 获取后台导航
                        this.getAdminMenu()
                        this.menuType = [
                            { id: 1, label: "系统页面", value: "system" },
                            { id: 2, label: "插件", value: "plugin" },
                            { id: 3, label: "自定义", value: "custom" },
                        ]
                    }
                },
                onDragSort() {

                },
                onStart() {

                },
                // 二级导航中的拖拽事件
                lv2OnMove(e) {
                    let isOne = false;
                    if (e.relatedContext.element !== undefined) {
                        const id = e.relatedContext.element.id
                        this.menuList.forEach(item => {
                            if (item.id === id) {
                                isOne = true
                            }
                        })
                        this.isLv1 = isOne
                    } else {
                        this.isLv1 = false
                    }
                },
                // 一级导航的拖拽中事件
                onMove(e, e1) {
                    let isOne = false;
                    if (e.relatedContext.element !== undefined) {
                        const id = e.relatedContext.element.id
                        this.menuList.forEach(item => {
                            if (item.id === id) {
                                isOne = true
                            }
                        })
                        this.isLv1 = isOne
                    } else {
                        this.isLv1 = false
                    }

                    // 拖拽中的一级节点存在子节点不允许 成为二级节点
                    if (e.draggedContext.element.child && e.draggedContext.element.child.length > 0) {
                        if (!isOne) {
                            return false
                        }
                    }

                    return true
                },
                // 鼠标左键按下
                getMouseDown(e, item) {
                    this.startXy = {
                        x: e.clientX,
                        y: e.clientY
                    }
                    this.draggleItem = item
                    this.moveId = item.id
                },
                // 鼠标移动
                getMouseMove(e) {
                    this.endXy = {
                        x: e.clientX,
                        y: e.clientY
                    }
                },
                // 松开鼠标左键与 vue.draggleable 的拖拽结束事件冲突 这里用拖拽结束事件
                // 拖拽结束
                onEnd() {
                    this.moveId = 0
                    // y轴上拖动的距离
                    let y = this.endXy.y - this.startXy.y
                    // x轴上拖动的距离
                    let x = this.endXy.x - this.startXy.x
                    if (-10 < y && y < 10) {
                        // 判断endXy 和 startXy的位置
                        if (x > 10) {
                            // 有子导航不的话不能变成二级导航
                            // 没有自导航的话 变成上一个一级导航的二级导航
                            if (this.draggleItem.child && this.draggleItem.child.length > 0) {
                                this.$message.warning("该导航存在二级子导航请清空后再尝试")
                            } else {
                                let isLevel2 = true
                                // 判断是否是二级导航
                                this.menuList.forEach(item => {
                                    if (item.id === this.draggleItem.id) {
                                        isLevel2 = false
                                    }
                                })
                                if (isLevel2) {
                                    this.$message.warning("该导航已经是二级导航了")
                                } else {
                                    // 一级导航，查找其上一个导航 插入到child中
                                    let index = this.menuList.findIndex(item => item.id === this.draggleItem.id)
                                    // 不为数组第一个元素
                                    if (index != 0) {
                                        this.menuList[index - 1].child.push(this.draggleItem)
                                        this.menuList = this.menuList.filter(item => {
                                            return item.id !== this.draggleItem.id
                                        })
                                    }
                                }
                            }
                        }
                        if (x < -10) {
                            // 判断是否为一级导航
                            let isLevel1 = false
                            this.menuList.forEach(item => {
                                if (item.id === this.draggleItem.id) {
                                    isLevel1 = true
                                }
                            })

                            if (isLevel1) {
                                this.$message.warning("该导航已经是一级导航了")
                            } else {
                                // 1.查找该二级导航的一级导航的id 并清除该二级导航

                                let pId = 0
                                for (let i = 0; i < this.menuList.length; i++) {
                                    if (this.menuList[i].child && this.menuList[i].child.length > 0) {
                                        this.menuList[i].child = this.menuList[i].child.filter(n => {
                                            if (n.id === this.draggleItem.id) {
                                                pId = this.menuList[i].id
                                            }
                                            return n.id !== this.draggleItem.id
                                        })
                                    }
                                }
                                // 查找父导航的下标
                                let index = this.menuList.findIndex(item => item.id === pId)
                                // 插入父导航之前
                                this.menuList.splice(index, 0, this.draggleItem)
                            }
                        }
                    }

                },
                // 应用导航点击事件
                subMenu() {
                    // let menu = JSON.parse(JSON.stringify(this.menuList));
                    // menu.map(item=>{
                    //     if(item.product_id){
                    //         item.product_id.map(n=>{
                    //             return n=n.splite('-')[0]
                    //         })
                    //     }
                    // })
                    // console.log(menu);
                    const params = {
                        menu: this.menuList
                    }
                    if (this.value == 1) {
                        // 保存前台的导航
                        saveHomeMenu(params).then(res => {
                            if (res.data.status = 200) {
                                this.$message.success(res.data.msg)
                            }
                        }).catch(error => {
                            this.$message.error(error.data.msg)
                        })
                    }
                    if (this.value == 2) {
                        // 保存后台的导航
                        saveAdminMenu(params).then(res => {
                            if (res.data.status === 200) {
                                this.$message.success(res.data.msg)
                                // 调用获取后台导航，保存到locastorage 并刷新页面
                                leftMenu().then(res=>{
                                    localStorage.setItem('backMenus', JSON.stringify(res.data.data.menu))
                                    location.reload();
                                })
                            }
                        }).catch(error => {
                            this.$message.error(error.data.msg)
                        })
                    }
                },
                // 导航点击事件
                itemClick(item) {
                    this.moveId = 0
                    this.activeId = item.id

                    // 判断是否有子导航
                    if (JSON.stringify(item.language) != "{}") {
                        item.isChecked = true
                    }


                    if (item.product_id && !item.product_id.length) {
                        this.formData = { ...item, product_id: [] }

                    } else {
                        this.formData = { ...item }
                    }


                    // 判断页面类型 给目标页面选择框赋值
                    // 系统页面
                    if (this.formData.type === 'system') {
                        this.selectList = this.systemList
                    }
                    // 插件
                    if (this.formData.type === 'plugin') {
                        this.selectList = this.pluginList
                    }
                    // 模块
                    if (this.formData.module) {
                        this.getProduct(this.formData.module)
                    }

                    this.isShowSet = true

                    // // 判断页面类型 给目标页面选择框赋值
                    // // 系统页面
                    // if (this.formData.type === 'system') {
                    //     this.selectList = this.systemList
                    // }
                    // // 插件
                    // if (this.formData.type === 'plugin') {
                    //     this.selectList = this.pluginList
                    // }
                    // // 模块
                    // if (this.formData.module) {
                    //     this.getProduct(this.formData.module)
                    // }
                },
                // 弹窗相关
                // 取消按钮点击事件
                close() {
                    this.visible = false
                    this.popupVisible = false
                    this.backPopupVisible = false
                    this.newPopupVisible = false
                    this.newBackPopupVisible = false
                },
                // 右侧设置页面 保存按钮点击事件
                saveSet() {

                    if (this.formData.type == 'custom') {
                        console.log("自定义");
                        if (!this.formData.url) {
                            this.$message.warning("url不能为空")
                            return false
                        }

                    } else if (this.formData.type != 'module') {

                        // if (this.value == 1) {
                            if (!this.formData.nav_id) {
                                this.$message.warning("请选择页面")
                                return false
                            }
                        // } else {
                        //     if (!this.formData.url) {
                        //         this.$message.warning("请选择页面")
                        //         return false
                        //     }
                        // }

                    }

                    if (this.formData.type == 'module') {
                        if (!this.formData.module) {
                            this.$message.warning("请选择模块类型")
                            return false
                        }
                    }

                    if (!this.formData.name) {
                        this.$message.warning("请输入导航名称")
                        return false
                    }

                    const id = this.formData.id
                    console.log(this.formData);
                    a: for (let i = 0; i < this.menuList.length; i++) {
                        if (this.menuList[i].id === id) {
                            if (!this.formData.isChecked) {
                                // this.formData.delete
                                this.formData.language = {}
                            }
                            this.menuList[i] = this.formData
                            break a;
                        } else {
                            if (this.menuList[i].child && this.menuList[i].child.length > 0) {
                                for (let j = 0; j < this.menuList[i].child.length; j++) {
                                    if (this.menuList[i].child[j].id === id) {
                                        this.menuList[i].child[j] = this.formData
                                        break a;
                                    }
                                }
                            }
                        }
                    }
                    this.popupVisible = false
                    this.backPopupVisible = false
                    this.newPopupVisible = false
                    this.newBackPopupVisible = false
                    // this.isShowSet = false
                    // this.$message.success("保存成功")


                },
                // 右侧设置页面 删除按钮点击事件
                delNav() {
                    // 弹框提醒
                    const confirmDia = this.$dialog.confirm({
                        header: '你确定要删除该项么？',
                        confirmBtn: '确定',
                        cancelBtn: '取消',
                        onConfirm: ({ e }) => {
                            // 导航id
                            const id = this.formData.id
                            this.menuList = this.menuList.filter(item => {
                                if (item.child && item.child.length > 0) {
                                    item.child = item.child.filter(n => {
                                        return n.id !== id
                                    })
                                }
                                return item.id !== id
                            })
                            this.isShowSet = false
                            // 请求成功后，销毁弹框
                            confirmDia.destroy();
                        },
                        onClose: ({ e, trigger }) => {
                            confirmDia.hide();
                        },
                    });
                },
                // 右侧设置页面 页面类型选择框改变时
                typeChange() {
                    // 根据选择的类型 给目标类型选择框赋值
                    // 系统
                    if (this.formData.type === 'system') {
                        this.selectList = [...this.systemList]
                    }
                    // 插件
                    if (this.formData.type === 'plugin') {
                        this.selectList = [...this.pluginList]
                    }
                    // 模块
                    if (this.formData.type === 'module') {
                        this.selectList = [...this.moduleList]
                        this.formData.module = ''
                    }
                    this.formData.url = ''

                    this.formData.url = ""
                    this.formData.icon = ""
                    this.formData.name = ""
                    this.formData.nav_id = ""
                    if (this.formData.product_id) {
                        this.formData.product_id = []
                    }



                    this.saveSet()
                },
                // 新建页面弹窗 页面类型选择框改变时
                newTypeChange() {
                    if (this.newFormData.type === 'system') {
                        this.selectList = [...this.systemList]
                    }
                    // 插件
                    if (this.newFormData.type === 'plugin') {
                        this.newFormData.nav_id = ""
                        this.selectList = [...this.pluginList]
                    }

                    this.newFormData.url = ""
                    this.newFormData.icon = ""
                    this.newFormData.name = ""
                    this.newFormData.nav_id = ""
                },
                // 新增页面弹窗保存按钮点击事件
                confirmNewMenu() {
                    this.popupVisible = false
                    this.backPopupVisible = false
                    this.newPopupVisible = false
                    this.newBackPopupVisible = false


                    if (!this.newFormData.name) {
                        this.$message.warning("请输入导航名称")
                        return false
                    }

                    if (this.newFormData.type == 'custom') {
                        if (!this.newFormData.url) {
                            this.$message.warning("请输入url")
                            return false
                        }
                    }

                    if (this.newFormData.type == 'module') {
                        if (!this.newFormData.module) {
                            this.$message.warning("请选择模块类型")
                            return false
                        }
                    }

                    if (this.newFormData.type == 'system' || this.newFormData.type == 'plugin') {

                        if (!this.newFormData.nav_id) {
                            this.$message.warning("请选择页面")
                            return false
                        }
                    }


                    // 判断是否是 自定义页面
                    if (this.newFormData.type !== 'custom') {
                        // 不是是自定义页面
                        // 通过 目标页面和 页面类型获取nav_id
                        this.selectList.forEach(item => {
                            if (item.url === this.newFormData.url) {
                                this.newFormData.nav_id = item.id
                            }
                        })
                    }
                    let id = 0
                    this.menuList.forEach(item => {
                        id += Number(item.id)
                    })
                    // 给一个唯一id
                    this.newFormData.id = id
                    let newPage = { ...this.newFormData, child: [] }

                    this.menuList.push(newPage)
                    this.visible = false

                    this.itemClick(newPage)
                },
                // confirmNewMenu({ validateResult, firstError }) {
                //     if (validateResult === true) {
                //         // 判断是否是 自定义页面
                //         if (this.newFormData.type !== 'custom') {
                //             // 不是是自定义页面
                //             // 通过 目标页面和 页面类型获取nav_id
                //             this.selectList.forEach(item => {
                //                 if (item.url === this.newFormData.url) {
                //                     this.newFormData.nav_id = item.id
                //                 }
                //             })
                //         }
                //         let id = 0
                //         this.menuList.forEach(item => {
                //             id += Number(item.id)
                //         })
                //         // 给一个唯一id
                //         this.newFormData.id = id
                //         const newPage = { ...this.newFormData, child: [] }
                //         this.menuList.push(newPage)
                //         this.visible = false
                //     } else {
                //         this.$message.warning(firstError);
                //     }

                // },
                // 点击新建页面按钮
                showNewMenuDialog() {
                    this.visible = true
                    this.isShowSet = false
                    this.newFormData = {
                        id: "",
                        type: "system",
                        url: "",
                        icon: "",
                        name: "",
                        nav_id: "",
                        isChecked: false,
                        language: {}
                    }
                    this.selectList = [...this.systemList]
                },
                // 右侧设置目标页面选择框改变
                urlSelectChange() {
                    const url = this.formData.url
                    // 页面类型为 系统页面
                    if (this.formData.type == 'system') {
                        this.selectList.forEach(item => {
                            if (item.url === url) {
                                this.formData.nav_id = item.id
                            }
                        })
                    }
                    // 页面类型为插件
                    if (this.formData.type == 'plugin') {
                        this.selectList.forEach(list => {
                            list.navs.forEach(item => {
                                if (item.url === url) {
                                    this.formData.nav_id = item.id
                                }
                            })
                        })
                    }

                    this.saveSet()
                },
                // 新增页面 目标页面选择框改变
                newUrlSelectChange() {
                    const url = this.newFormData.url
                    // 页面类型为 系统页面
                    if (this.newFormData.type == 'system') {
                        this.selectList.forEach(item => {
                            if (item.url === url) {
                                this.newFormData.nav_id = item.id
                            }
                        })
                    }
                    // 页面类型为插件
                    if (this.newFormData.type == 'plugin') {
                        this.newFormData.nav_id = ''
                        this.selectList.forEach(list => {
                            list.navs.forEach(item => {
                                if (item.url === url) {
                                    this.newFormData.nav_id = item.id
                                }
                            })
                        })
                    }

                },
                // 展示所有icon图标
                showIconList() {
                    this.popupVisible = true
                },
                getAllIcon() {
                    let url = "/upload/common/iconfont/iconfont.json"
                    let _this = this

                    // 申明一个XMLHttpRequest
                    let request = new XMLHttpRequest();
                    // 设置请求方法与路径
                    request.open("get", url);
                    // 不发送数据到服务器
                    request.send(null);
                    //XHR对象获取到返回信息后执行
                    request.onload = function () {
                        // 解析获取到的数据
                        let data = JSON.parse(request.responseText);
                        _this.iconsData = data.glyphs
                        _this.iconsData.map(item => {
                            item.font_class = "icon-" + item.font_class
                        })
                    }

                },
                // 通过模块获取商品列表
                getProduct(module) {
                    this.newModuleSelectLoading = true
                    const params = {
                        module
                    }
                    productBymodule(params).then(res => {
                        if (res.data.status === 200) {
                            this.productList = res.data.data.list
                            this.changeId(this.productList)
                        }
                        this.newModuleSelectLoading = false
                    }).catch(err => {
                        this.newModuleSelectLoading = false
                    })
                },
                // 将关联页面的id改变 
                changeId(list) {
                    list.map(item => {
                        if (item.child && item.child.length > 0) {
                            item.id = item.id + '-' + item.name
                            this.changeId(item.child)
                        }
                    })
                },
                newModuleChange() {
                    const module = this.newFormData.module
                    this.newFormData = {
                        ...this.newFormData, product_id: []
                    }
                    // this.newFormData.product_id = []
                    this.getProduct(module)
                },
                moduleChange() {
                    const module = this.formData.module
                    this.formData.product_id = []
                    this.getProduct(module)

                    this.saveSet()
                },
                urlInputChange() {
                    this.saveSet()
                },
                nameInputChange() {
                    this.saveSet()
                }
            },
            created() {
                this.getAllIcon()
                // 默认拉取前台菜单
                this.getHomeMenu()
            },
        }).$mount(template)
        typeof old_onload == 'function' && old_onload()
    };
})(window);

