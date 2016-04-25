
import {Component} from 'angular2/core';
import {Invoice} from "./invoice.model";
import {Input} from "angular2/core";
import {Control} from "angular2/common";
import {Validators} from "angular2/common";
import {Output} from "angular2/core";
import {FormBuilder} from "angular2/common";
import {ControlGroup} from "angular2/common";
import {Form} from "angular2/common";
import {FORM_DIRECTIVES} from "angular2/common";
import {ControlArray} from "angular2/common";
import {ChangeDetectorRef} from "angular2/core";
import {Observable} from "rxjs/Observable";
import {ChangeDetectionStrategy} from "angular2/core";
import {SimpleChange} from "angular2/core";

@Component({
    selector: 'invoice-form',
    templateUrl: 'app/invoice/form/invoice.html',
    directives: [FORM_DIRECTIVES],
    changeDetection: ChangeDetectionStrategy.OnPush
})
export class InvoiceFormComponent {
    @Input() invoice:Invoice;
    invoiceForm:ControlGroup;
    private itemsArray:ControlArray;
    private changeDetector:ChangeDetectorRef;

    constructor(fb: FormBuilder, changeDetector:ChangeDetectorRef) {
        this.itemsArray = new ControlArray([]);
        this.changeDetector = changeDetector;

        this.invoiceForm = fb.group({
            'customerName':  ['', Validators.required],
            'streetName':  ['', Validators.required],
            'postCode':  ['', Validators.required],
            'city':  ['', Validators.required],
            'email': ['', Validators.compose([Validators.required, Validators.pattern('.+@.+\..{0,10}')])],
            'created': ['', Validators.compose([Validators.required, Validators.pattern('[0-9]{4}-[0-9]{2}-[0-9]{2}')])],
            'due': ['', Validators.compose([Validators.required, Validators.pattern('[0-9]{4}-[0-9]{2}-[0-9]{2}')])],
            'items': [],
        });
    }

    public isValid() : boolean {
        this.changeDetector.detectChanges();

        return this.invoiceForm.valid
            && this.invoice.items.length !== 0
            && this.itemsArray.valid;
    }

    ngOnInit() {
        for (var i in this.invoice.items) {
            this.addControlItem();
        }
    }

    public addItem() : void {
        this.invoice.addItem();
        this.addControlItem();
    }

    private addControlItem() : void {
        this.itemsArray.push(new ControlGroup({
            description: new Control('', Validators.compose([Validators.required, Validators.maxLength(255)])),
            amount: new Control('', Validators.compose([Validators.required, Validators.pattern('[0-9]{1,9}')])),
            tax: new Control('', Validators.compose([Validators.required, Validators.pattern('[0-9]{1,2}')])),
            price: new Control('', Validators.compose([Validators.required, Validators.pattern('[0-9]{1,7}(,[0-9]{2})?')]))
        }));
    }

    public removeItem(i:number) :void {
        this.invoice.removeItem(this.invoice.items[i]);
        this.itemsArray.removeAt(i);
    }
}
