
import {Component} from 'angular2/core';
import {InvoiceService} from "../invoice.service";
import {Invoice} from "../invoice.model";
import {InvoiceFormComponent} from "../invoice-form.component";
import {RouteParams} from "angular2/router";
import {Router} from "angular2/router";
import {ChangeDetectorRef} from "angular2/core";

@Component({
    templateUrl: 'app/invoice/edit/edit.html',
    directives: [InvoiceFormComponent]
})
export class EditInvoiceComponent {
    private invoiceService:InvoiceService;
    private invoice:Invoice;
    private id:number;
    private router:Router;
    private changeDetector:ChangeDetectorRef;

    constructor(invoiceService:InvoiceService, routeParams:RouteParams, router:Router, changeDetector:ChangeDetectorRef) {
        this.router = router;
        this.invoiceService = invoiceService;

        // bugfix for invoice is initialized after content update..
        // https://github.com/angular/angular/issues/6005
        this.changeDetector = changeDetector;
        this.id = Number(routeParams.get('id'));

        this.invoiceService.getInvoice(this.id).subscribe((invoice:Invoice) => {
            this.invoice = invoice;
            this.changeDetector.detectChanges();
        });
    }

    edit():void {
        this.invoiceService.update(this.invoice).subscribe(() => {
            this.router.navigate(['ListInvoices']);
        });
    }

    remove():void {
        if (confirm('Are you sure you want delete the invoice?')) {
            this.invoiceService.remove(this.invoice).subscribe(() => {
                this.router.navigate(['ListInvoices']);
            });
        }
    }
}
