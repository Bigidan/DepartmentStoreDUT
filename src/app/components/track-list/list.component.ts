import {Component, EventEmitter, Input, Output, SimpleChanges} from '@angular/core';
import {FilterGroupComponent} from "../filter-group/filter-group.component";
import {DataService} from "../../services/data.service";
import { HttpClientModule } from '@angular/common/http';
import {NgIf} from "@angular/common";
import {ItemListComponent} from "../item-list/item-list.component";

@Component({
  selector: 'app-list',
  standalone: true,
  imports: [
    HttpClientModule,
    FilterGroupComponent,
    NgIf,
    ItemListComponent
  ],
  templateUrl: "list.component.html",
  styleUrl: "list.component.css",
  providers: [DataService],
})
export class ListComponent {
  @Input() selectedItem: string = 'storage';
  @Output() itemSelected = new EventEmitter<any>();

  filter_1 = [];
  filter_2 = [];

  saved_filter_1: string[] = [];
  saved_filter_2: string[] = [];

  items = [];

  constructor(private dataService: DataService) {}

  ngOnInit() {
    console.log(this.selectedItem);
    this.getData('filters', this.selectedItem);
  }

  ngOnChanges(changes: SimpleChanges) {
    if (changes['selectedItem']) {
      this.getData('filters', this.selectedItem);
    }
  }

  onItemSelected(item: any) {
    this.itemSelected.emit(item);
  }

  getData(mode: string, selectedNavItem: string, filters_1: any[] = [], filters_2: any[] = []) {
    this.dataService.getData(mode, selectedNavItem, filters_1, filters_2).subscribe(data => {
      if (mode === 'filters') {
        this.filter_1 = data.filter_1;
        this.filter_2 = data.filter_2;
      } else if (mode === 'items') {
        this.items = data.items;
        console.log(this.items);
      }
    });
  }

  onActiveItemsChange(event: {group: string, activeItems: Set<string>}) {
    console.log(`Група: ${event.group}, Вибрані: ${Array.from(event.activeItems).join(', ')}`);
    if (event.group == "filter_1") {
      this.saved_filter_1 = Array.from(event.activeItems);
    }
    else {
      this.saved_filter_2 = Array.from(event.activeItems);
    }
    this.getData('items', this.selectedItem, this.saved_filter_1, this.saved_filter_2);
  }
}
