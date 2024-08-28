import {Component, EventEmitter, Input, OnChanges, Output, SimpleChanges} from '@angular/core';
import {ItemElementComponent} from "../item-element/item-element.component";
import {NgClass, NgForOf} from "@angular/common";

@Component({
  selector: 'app-item-list',
  standalone: true,
  imports: [
    ItemElementComponent,
    NgForOf,
    NgClass
  ],
  templateUrl: "item-list.component.html",
  styleUrls: ['item-list.component.css']
})
export class ItemListComponent implements OnChanges {
  @Input() items: any[] = [];
  @Input() selectedNavItem: any;

  @Output() itemSelected = new EventEmitter<any>();
  activeItemId: any = null;

  ngOnChanges(changes: SimpleChanges) {
    if (changes["selectedNavItem"]) {
      this.clearSelection();
    }
  }

  clearSelection() {
    this.activeItemId = null;
    this.items = [];
  }

  onItemSelected(item: any) {
    this.activeItemId = item.id;
    this.itemSelected.emit(item);
  }

  isActive(item: any): boolean {
    return item.id === this.activeItemId;
  }
}
