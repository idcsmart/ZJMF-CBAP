/* 处理UI库多语言 */
const language = {
  template:
    `
      <t-config-provider :global-config="calcLang">
        <slot></slot>
      </t-config-provider>
    `,
  data () {
    return {
      language: localStorage.getItem('backLang') || 'zh-cn',
      enGlobalConfig: {
        pagination: {
          itemsPerPage: '{size} / page',
          jumpTo: 'Jump to',
          page: '',
          total: '{total} items',
        },
        cascader: {
          empty: 'Empty Data',
          loadingText: 'loading...',
          placeholder: 'please select',
        },
        calendar: {
          yearSelection: '{year}',
          monthSelection: '{month}',
          yearRadio: 'year',
          monthRadio: 'month',
          hideWeekend: 'Hide Week',
          showWeekend: 'Show Week',
          today: 'Today',
          thisMonth: 'This Month',
          week: 'Monday,Tuesday,Wedsday,Thuresday,Friday,Staturday,Sunday',
          cellMonth:
            'January,February,March,April,May,June,July,August,September,October,November,December',
        },
        transfer: {
          title: '{checked} / {total}',
          empty: 'Empty Data',
          placeholder: 'enter keyworkd to search',
        },
        timePicker: {
          dayjsLocale: 'en',
          now: 'Now',
          confirm: 'Confirm',
          anteMeridiem: 'AM',
          postMeridiem: 'PM',
          placeholder: 'please select',
        },
        dialog: {
          confirm: 'Confirm',
          cancel: 'Cancel',
        },
        drawer: {
          confirm: 'Confirm',
          cancel: 'Cancel',
        },
        popconfirm: {
          confirm: {
            content: 'OK',
          },
          cancel: {
            content: 'Cancel',
          },
        },
        table: {
          empty: 'Empty Data',
          loadingText: 'loading...',
          loadingMoreText: 'loading more',
          filterInputPlaceholder: '',
          sortAscendingOperationText: 'click to sort ascending',
          sortCancelOperationText: 'click to cancel sorting',
          sortDescendingOperationText: 'click to sort descending',
          clearFilterResultButtonText: 'Clear',
          columnConfigButtonText: 'Column Config',
          columnConfigTitleText: 'Table Column Config',
          columnConfigDescriptionText:
            'Please select columns to show them in the table',
          confirmText: 'Confirm',
          cancelText: 'Cancel',
          resetText: 'Reset',
          selectAllText: 'Select All',
          searchResultText: 'Search "{result}". Find {count} items.',
        },
        select: {
          empty: 'Empty Data',
          loadingText: 'loading...',
          placeholder: 'please select',
        },
        tree: {
          empty: 'Empty Data',
        },
        treeSelect: {
          empty: 'Empty Data',
          loadingText: 'loading...',
          placeholder: 'please select',
        },
        datePicker: {
          dayjsLocale: 'en',
          placeholder: {
            date: 'select date',
            month: 'select month',
            year: 'select year',
          },
          weekdays: ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'],
          months: [
            'Jan',
            'Feb',
            'Mar',
            'Apr',
            'May',
            'Jun',
            'Jul',
            'Aug',
            'Sep',
            'Oct',
            'Nov',
            'Dec',
          ],
          quarters: ['Q1', 'Q2', 'Q3', 'Q4'],
          rangeSeparator: ' - ',
          direction: 'ltr',
          format: 'YYYY-MM-DD',
          dayAriaLabel: 'D',
          yearAriaLabel: 'Y',
          monthAriaLabel: 'M',
          weekAbbreviation: 'W',
          confirm: 'Confirm',
          selectTime: 'Select Time',
          selectDate: 'Select Date',
          nextYear: 'Next Year',
          preYear: 'Last Year',
          nextMonth: 'Next Month',
          preMonth: 'Last Month',
          preDecade: 'Last Decade',
          nextDecade: 'Next Decade',
          now: 'Now',
        },
        upload: {
          sizeLimitMessage: 'File is too large to upload. {sizeLimit}',
          cancelUploadText: 'Cancel',
          triggerUploadText: {
            fileInput: 'Upload',
            image: 'Click to upload',
            normal: 'Upload',
            reupload: 'ReUpload',
            continueUpload: 'Continue Upload',
            delete: 'Delete',
            uploading: 'Uploading',
          },
          dragger: {
            dragDropText: 'Drop here',
            draggingText: 'Drag file to this area to upload',
            clickAndDragText: 'Click "Upload" or Drag file to this area to upload',
          },
          file: {
            fileNameText: 'filename',
            fileSizeText: 'size',
            fileStatusText: 'status',
            fileOperationText: 'operation',
            fileOperationDateText: 'date',
          },
          progress: {
            uploadingText: 'Uploading',
            waitingText: 'Waiting',
            failText: 'Failed',
            successText: 'Success',
          },
        },
        form: {
          errorMessage: {
            date: '${name} is invalid',
            url: '${name} is invalid',
            required: '${name} is required',
            max: '${name} must be at least ${validate} characters',
            min: '${name} cannot be longer than ${validate} characters',
            len: '${name} must be exactly ${validate} characters',
            enum: '${name} must be one of ${validate}',
            idcard: '${name} is invalid',
            telnumber: '${name} is invalid',
            pattern: '${name} is invalid',
            validator: '${name} is invalid',
            boolean: '${name} is not a boolean',
            number: '${name} must be a number',
          },
        },
        input: {
          placeholder: 'please enter',
        },
        list: {
          loadingText: 'loading...',
          loadingMoreText: 'loading more',
        },
        alert: {
          expandText: 'expand',
          collapseText: 'collapse',
        },
        anchor: {
          copySuccessText: 'copy the link successfully',
          copyText: 'copy link',
        },
        colorPicker: {
          swatchColorTitle: 'System Default',
          recentColorTitle: 'Recently Used',
          clearConfirmText: 'Clear recently used colors?',
        },
        guide: {
          finishButtonProps: {
            content: 'Finish',
            theme: 'primary',
          },
          nextButtonProps: {
            content: 'Next Step',
            theme: 'primary',
          },
          skipButtonProps: {
            content: 'Skip',
            theme: 'default',
          },
          prevButtonProps: {
            content: 'Last Step',
            theme: 'default',
          },
          image: {
            errorText: 'unable to load',
            loadingText: 'loading',
          },
          imageViewer: {
            errorText: 'unable to load',
            mirrorTipText: 'mirror',
            rotateTipText: 'rotate',
            originalSizeTipText: 'original',
          },
        }
      },
      zkGlobalConfig: {
        pagination: {
          itemsPerPage: '{size} 項/頁',
          jumpTo: '跳至',
          page: '頁',
          total: '共 {total} 項數據',
        },
        cascader: {
          empty: '暫無數據',
          loadingText: '載入中',
          placeholder: '請選擇',
        },
        calendar: {
          yearSelection: '{year} 年',
          monthSelection: '{month} 月',
          yearRadio: '年',
          monthRadio: '月',
          hideWeekend: '隱藏週末',
          showWeekend: '顯示週末',
          today: '今天',
          thisMonth: '本月',
          week: '一,二,三,四,五,六,日',
          cellMonth: '1 月,2 月,3 月,4 月,5 月,6 月,7 月,8 月,9 月,10 月,11 月,12 月',
        },
        transfer: {
          title: '{checked} / {total} 項',
          empty: '暫無數據',
          placeholder: '請輸入關鍵詞搜尋',
        },
        timePicker: {
          dayjsLocale: 'zh-tw',
          now: '此刻',
          confirm: '確認',
          anteMeridiem: '上午',
          postMeridiem: '下午',
          placeholder: '選擇時間',
        },
        dialog: {
          confirm: '確認',
          cancel: '取消',
        },
        drawer: {
          confirm: '確認',
          cancel: '取消',
        },
        popconfirm: {
          confirm: {
            content: '確認',
          },
          cancel: {
            content: '取消',
          },
        },
        table: {
          empty: '暫無數據',
          loadingText: '正在載入中，請稍後',
          loadingMoreText: '點擊載入更多',
          filterInputPlaceholder: '請輸入内容（無默認值）',
          sortAscendingOperationText: '點擊升序',
          sortCancelOperationText: '點擊取消排序',
          sortDescendingOperationText: '點擊降序',
          clearFilterResultButtonText: '清空篩選',
          columnConfigButtonText: '行配置',
          columnConfigTitleText: '表格行配置',
          columnConfigDescriptionText: '請選擇需要在表格中顯示的數據行',
          confirmText: '確認',
          cancelText: '取消',
          resetText: '重置',
          selectAllText: '全選',
          searchResultText: '搜尋"{result}"，找到{count}項結果',
        },
        select: {
          empty: '暫無數據',
          loadingText: '載入中',
          placeholder: '請選擇',
        },
        tree: {
          empty: '暫無數據',
        },
        treeSelect: {
          empty: '暫無數據',
          loadingText: '載入中',
          placeholder: '請選擇',
        },
        datePicker: {
          dayjsLocale: 'zh-tw',
          placeholder: {
            date: '請選擇日期',
            month: '請選擇月份',
            year: '請選擇年份',
          },
          weekdays: ['一', '二', '三', '四', '五', '六', '日'],
          months: [
            '1月',
            '2月',
            '3月',
            '4月',
            '5月',
            '6月',
            '7月',
            '8月',
            '9月',
            '10月',
            '11月',
            '12月',
          ],
          quarters: ['一季度', '二季度', '三季度', '四季度'],
          rangeSeparator: ' - ',
          direction: 'ltr',
          format: 'YYYY-MM-DD',
          dayAriaLabel: '日',
          weekAbbreviation: '週',
          yearAriaLabel: '年',
          monthAriaLabel: '月',
          comfirm: '確認',
          selectTime: '選擇時間',
          selectDate: '選擇日期',
          nextYear: '下一年',
          preYear: '上一年',
          nextMonth: '下個月',
          preMonth: '上個月',
          preDecade: '上十年',
          nextDecade: '下十年',
          now: '當前',
        },
        upload: {
          sizeLimitMessage: '文件大小不能超過 {sizeLimit}',
          cancelUploadText: '取消上傳',
          triggerUploadText: {
            fileInput: '選擇文件',
            image: '點擊上傳圖片',
            normal: '點擊上傳',
            // 選擇文件和上傳文件是 2 個步驟，文本需明確步驟
            reupload: '重新選擇',
            continueUpload: '繼續選擇',
            delete: '刪除',
            uploading: '上傳中',
          },
          dragger: {
            dragDropText: '釋放鼠標',
            draggingText: '拖拽到此區域',
            clickAndDragText: '點擊上方“選擇文件”或將文件拖拽到此區域',
          },
          file: {
            fileNameText: '文件名',
            fileSizeText: '文件大小',
            fileStatusText: '狀態',
            fileOperationText: '操作',
            fileOperationDateText: '上傳日期',
          },
          progress: {
            uploadingText: '正在上傳',
            waitingText: '等待上傳',
            failText: '上傳失敗',
            successText: '上傳成功',
          },
        },
        form: {
          errorMessage: {
            date: '請輸入正確的${name}',
            url: '請輸入正確的${name}',
            required: '${name}必填',
            max: '${name}字符長度不能超過 ${validate} 個字符，一個中文等於兩個字符',
            min: '${name}字符長度不能少於 ${validate} 個字符，一個中文等於兩個字符',
            len: '${name}字符長度必須是 ${validate}',
            enum: '${name}只能是${validate}等',
            idcard: '請輸入正確的${name}',
            telnumber: '請輸入正確的${name}',
            pattern: '請輸入正確的${name}',
            validator: '${name}不符合要求',
            boolean: '${name}數據類型必須是布林類型',
            number: '${name}必須是數字',
          },
        },
        input: {
          placeholder: '請輸入',
        },
        list: {
          loadingText: '正在載入中，請稍後',
          loadingMoreText: '點擊載入更多',
        },
        alert: {
          expandText: '展開更多',
          collapseText: '收起',
        },
        anchor: {
          copySuccessText: '連結複製成功',
          copyText: '複製連結',
        },
        colorPicker: {
          swatchColorTitle: '系統預設顔色',
          recentColorTitle: '最近使用的顔色',
          clearConfirmText: '確定清空最近使用的顔色嗎？',
        },
        guide: {
          finishButtonProps: {
            content: '完成',
            theme: 'primary',
          },
          nextButtonProps: {
            content: '下一步',
            theme: 'primary',
          },
          skipButtonProps: {
            content: '跳過',
            theme: 'default',
          },
          prevButtonProps: {
            content: '上一步',
            theme: 'default',
          },
        },
        image: {
          errorText: '圖片無法顯示',
          loadingText: '圖片載入中',
        },
        imageViewer: {
          errorText: '圖片載入失敗，可嘗試重新載入',
          mirrorTipText: '鏡像',
          rotateTipText: '旋轉',
          originalSizeTipText: '原始大小',
        }
      }
    }
  },
  computed: {
    calcLang () {
      switch (this.language) {
        case 'en-us':
          return this.enGlobalConfig
        case 'zh-hk':
          return this.zkGlobalConfig
        default:
          return {}
      }
    }
  }
}
