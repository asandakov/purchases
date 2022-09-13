BX.ready(function(){

    BX.Vue.create ({

        el: "#purchases-block",

        data: {
            msg: "",
            page: 1,
            isPage: true,
            count: 0,
            search: "",
            filter: {
                status: "0",
                date: "all",
            },
            purchases:[],

        },
        created() {
            this.filterPurchases();
        },

        watch: {
            search: function (val, old) {
                this.filterPurchases();
            },

        },

        methods: {

            filterPurchases() {
                this.page = 1;
                this.getPurchases();
            },

            pagePurchases() {
                this.getPurchases();
            },

            getPurchases() {

                let data = new FormData();
                data.set('search', this.search);
                data.set('filter', JSON.stringify(this.filter));
                data.set('page', this.page);

                this.msg = "";
                BX.ajax.runComponentAction('sibintek:purchases.list', 'getPurchases', {
                    mode: 'class',
                    data: data
                }).then((response) =>{
                    this.isPage = true;
                    if  (this.page == 1) {
                        this.purchases = response["data"]["list"];
                    }else{
                        this.purchases.push(...response["data"]["list"]);
                    }

                    this.count = response["data"]["count"];

                    if (this.count == this.purchases.length)
                        this.isPage = false;

                    this.page = this.page + 1;

                }, (response) => {
                    this.msg = "Техничская ошибка. Попробуйте позже.";
                });


            },

        }

    });
});
