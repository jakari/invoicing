
import {CreateInvoiceComponent} from './invoice/create/create.component';
import {Component} from 'angular2/core';
import {RouteConfig, ROUTER_DIRECTIVES} from 'angular2/router';
import {Router} from "angular2/router";
import {InvoiceService} from "./invoice/invoice.service";
import {ListInvoicesComponent} from "./invoice/list/list.component";
import {EditInvoiceComponent} from "./invoice/edit/edit.component";
import {ViewInvoiceComponent} from "./invoice/view/view.component";


@Component({
    selector: 'my-app',
    templateUrl: '/app/app.html',
    directives: [ROUTER_DIRECTIVES],
    providers: [InvoiceService]
})
@RouteConfig([
    {path: '/invoice/create', name: 'CreateInvoice', component: CreateInvoiceComponent},
    {path: '/invoice/edit/:id', name: 'EditInvoice', component: EditInvoiceComponent},
    {path: '/invoice/view/:id', name: 'ViewInvoice', component: ViewInvoiceComponent},
    {path: '/invoices', name: 'ListInvoices', component: ListInvoicesComponent, useAsDefault: true}
])
export class AppComponent {
    public title = 'Invoicing';
    private router:Router;

    constructor(router:Router) {
        this.router = router;
    }

    public isRouteActive(route:string[]):boolean {
        return this.router.isRouteActive(this.router.generate(route));
    }
}
